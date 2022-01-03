<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2013
 * @copyright Aimeos (aimeos.org), 2015-2022
 * @package Controller
 * @subpackage Order
 */


namespace Aimeos\Controller\Jobs\Order\Email\Payment;


/**
 * Order payment e-mail job controller.
 *
 * @package Controller
 * @subpackage Order
 */
class Standard
	extends \Aimeos\Controller\Jobs\Base
	implements \Aimeos\Controller\Jobs\Iface
{
	/**
	 * Returns the localized name of the job.
	 *
	 * @return string Name of the job
	 */
	public function getName() : string
	{
		return $this->context()->translate( 'controller/jobs', 'Order payment related e-mails' );
	}


	/**
	 * Returns the localized description of the job.
	 *
	 * @return string Description of the job
	 */
	public function getDescription() : string
	{
		return $this->context()->translate( 'controller/jobs', 'Sends order confirmation or payment status update e-mails' );
	}


	/**
	 * Executes the job.
	 *
	 * @throws \Aimeos\Controller\Jobs\Exception If an error occurs
	 */
	public function run()
	{
		$context = $this->context();
		$config = $context->config();

		$client = \Aimeos\Client\Html\Email\Payment\Factory::create( $context );

		$orderManager = \Aimeos\MShop::create( $context, 'order' );

		/** controller/jobs/order/email/payment/limit-days
		 * Only send payment e-mails of orders that were created in the past within the configured number of days
		 *
		 * The payment e-mails are normally send immediately after the payment
		 * status has changed. This option prevents e-mails for old order from
		 * being send in case anything went wrong or an update failed to avoid
		 * confusion of customers.
		 *
		 * @param integer Number of days
		 * @since 2014.03
		 * @category User
		 * @category Developer
		 * @see controller/jobs/order/email/delivery/limit-days
		 * @see controller/jobs/service/delivery/process/limit-days
		 */
		$limit = $config->get( 'controller/jobs/order/email/payment/limit-days', 30 );
		$limitDate = date( 'Y-m-d H:i:s', time() - $limit * 86400 );

		$default = array(
			\Aimeos\MShop\Order\Item\Base::PAY_REFUND,
			\Aimeos\MShop\Order\Item\Base::PAY_PENDING,
			\Aimeos\MShop\Order\Item\Base::PAY_AUTHORIZED,
			\Aimeos\MShop\Order\Item\Base::PAY_RECEIVED,
		);

		/** controller/jobs/order/email/payment/status
		 * Only send order payment notification e-mails for these payment status values
		 *
		 * Notification e-mail about payment status changes can be sent for these
		 * status values:
		 *
		 * * 0: deleted
		 * * 1: canceled
		 * * 2: refused
		 * * 3: refund
		 * * 4: pending
		 * * 5: authorized
		 * * 6: received
		 *
		 * User-defined status values are possible but should be in the private
		 * block of values between 30000 and 32767.
		 *
		 * @param integer Payment status constant
		 * @since 2014.03
		 * @category User
		 * @category Developer
		 * @see controller/jobs/order/email/delivery/status
		 * @see controller/jobs/order/email/payment/limit-days
		 */
		foreach( (array) $config->get( 'controller/jobs/order/email/payment/status', $default ) as $status )
		{
			$orderSearch = $orderManager->filter();

			$param = array( \Aimeos\MShop\Order\Item\Status\Base::EMAIL_PAYMENT, (string) $status );
			$orderFunc = $orderSearch->make( 'order:status', $param );

			$expr = array(
				$orderSearch->compare( '>=', 'order.mtime', $limitDate ),
				$orderSearch->compare( '==', 'order.statuspayment', $status ),
				$orderSearch->compare( '==', $orderFunc, 0 ),
			);
			$orderSearch->setConditions( $orderSearch->and( $expr ) );

			$start = 0;

			do
			{
				$items = $orderManager->search( $orderSearch );

				$this->process( $client, $items, $status );

				$count = count( $items );
				$start += $count;
				$orderSearch->slice( $start );
			}
			while( $count >= $orderSearch->getLimit() );
		}
	}


	/**
	 * Adds the status of the delivered e-mail for the given order ID
	 *
	 * @param string $orderId Unique order ID
	 * @param int $value Status value
	 */
	protected function addOrderStatus( string $orderId, int $value )
	{
		$orderStatusManager = \Aimeos\MShop::create( $this->context(), 'order/status' );

		$statusItem = $orderStatusManager->create();
		$statusItem->setParentId( $orderId );
		$statusItem->setType( \Aimeos\MShop\Order\Item\Status\Base::EMAIL_PAYMENT );
		$statusItem->setValue( $value );

		$orderStatusManager->save( $statusItem );
	}


	/**
	 * Returns the delivery address item of the order
	 *
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $orderBaseItem Order including address items
	 * @return \Aimeos\MShop\Order\Item\Base\Address\Iface Delivery or payment address item
	 * @throws \Aimeos\Controller\Jobs\Exception If no address item is available
	 */
	protected function getAddressItem( \Aimeos\MShop\Order\Item\Base\Iface $orderBaseItem ) : \Aimeos\MShop\Order\Item\Base\Address\Iface
	{
		$type = \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_PAYMENT;
		if( ( $addr = current( $orderBaseItem->getAddress( $type ) ) ) !== false ) {
			return $addr;
		};

		$msg = sprintf( 'No address found in order base with ID "%1$s"', $orderBaseItem->getId() );
		throw new \Aimeos\Controller\Jobs\Exception( $msg );
	}


	/**
	 * Returns an initialized view object
	 *
	 * @param \Aimeos\MShop\Context\Item\Iface $context Context item
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $orderBaseItem Complete order including addresses, products, services
	 * @param string|null $langId ISO language code, maybe country specific
	 * @return \Aimeos\MW\View\Iface Initialized view object
	 */
	protected function view( \Aimeos\MShop\Context\Item\Iface $context, \Aimeos\MShop\Order\Item\Base\Iface $orderBaseItem, string $langId = null ) : \Aimeos\MW\View\Iface
	{
		$view = $context->view();

		$params = [
			'locale' => $langId,
			'site' => $orderBaseItem->getSiteCode(),
			'currency' => $orderBaseItem->locale()->getCurrencyId()
		];

		$helper = new \Aimeos\MW\View\Helper\Param\Standard( $view, $params );
		$view->addHelper( 'param', $helper );

		$helper = new \Aimeos\MW\View\Helper\Number\Locale( $view, $langId );
		$view->addHelper( 'number', $helper );

		$helper = new \Aimeos\MW\View\Helper\Config\Standard( $view, $context->config() );
		$view->addHelper( 'config', $helper );

		$helper = new \Aimeos\MW\View\Helper\Mail\Standard( $view, $context->mail()->create() );
		$view->addHelper( 'mail', $helper );

		$helper = new \Aimeos\MW\View\Helper\Translate\Standard( $view, $context->i18n( $langId ) );
		$view->addHelper( 'translate', $helper );

		return $view;
	}


	/**
	 * Sends the payment e-mail for the given orders
	 *
	 * @param \Aimeos\Client\Html\Iface $client HTML client object for rendering the payment e-mails
	 * @param \Aimeos\Map $items List of order items implementing \Aimeos\MShop\Order\Item\Iface with their IDs as keys
	 * @param int $status Delivery status value
	 */
	protected function process( \Aimeos\Client\Html\Iface $client, \Aimeos\Map $items, int $status )
	{
		$context = $this->context();
		$orderBaseManager = \Aimeos\MShop::create( $context, 'order/base' );

		foreach( $items as $id => $item )
		{
			try
			{
				$orderBaseItem = $orderBaseManager->load( $item->getBaseId() );
				$addr = $this->getAddressItem( $orderBaseItem );

				if( $addr->getEmail() )
				{
					$this->processItem( $client, $item, $orderBaseItem, $addr );

					$str = sprintf( 'Sent order payment e-mail for status "%1$s" to "%2$s"', $status, $addr->getEmail() );
					$context->logger()->info( $str, 'email/order/payment' );
				}

				$this->addOrderStatus( $id, $status );
			}
			catch( \Exception $e )
			{
				$str = 'Error while trying to send payment e-mail for order ID "%1$s" and status "%2$s": %3$s';
				$msg = sprintf( $str, $item->getId(), $item->getStatusPayment(), $e->getMessage() );
				$context->logger()->error( $msg . PHP_EOL . $e->getTraceAsString(), 'email/order/payment' );
			}
		}
	}


	/**
	 * Sends the payment related e-mail for a single order
	 *
	 * @param \Aimeos\Client\Html\Iface $client HTML client object for rendering the payment e-mails
	 * @param \Aimeos\MShop\Order\Item\Iface $orderItem Order item the payment related e-mail should be sent for
	 * @param \Aimeos\MShop\Order\Item\Base\Iface $orderBaseItem Complete order including addresses, products, services
	 * @param \Aimeos\MShop\Order\Item\Base\Address\Iface $addrItem Address item to send the e-mail to
	 */
	protected function processItem( \Aimeos\Client\Html\Iface $client, \Aimeos\MShop\Order\Item\Iface $orderItem,
		\Aimeos\MShop\Order\Item\Base\Iface $orderBaseItem, \Aimeos\MShop\Order\Item\Base\Address\Iface $addrItem )
	{
		$context = $this->context();
		$langId = ( $addrItem->getLanguageId() ?: $orderBaseItem->locale()->getLanguageId() );

		$view = $this->view( $context, $orderBaseItem, $langId );
		$view->extAddressItem = $addrItem;
		$view->extOrderBaseItem = $orderBaseItem;
		$view->extOrderItem = $orderItem;

		$client->setView( $view );
		$client->header();
		$client->body();

		$context->mail()->send( $view->mail() );
	}
}
