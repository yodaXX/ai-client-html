<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2018-2022
 */


namespace Aimeos\Client\Html\Email\Voucher\Html;


class StandardTest extends \PHPUnit\Framework\TestCase
{
	private static $orderItem;
	private static $orderBaseItem;
	private $object;
	private $context;
	private $emailMock;
	private $view;


	public static function setUpBeforeClass() : void
	{
		$manager = \Aimeos\MShop\Order\Manager\Factory::create( \TestHelperHtml::context() );
		$orderBaseManager = $manager->getSubManager( 'base' );

		$search = $manager->filter();
		$search->setConditions( $search->compare( '==', 'order.datepayment', '2008-02-15 12:34:56' ) );

		if( ( self::$orderItem = $manager->search( $search )->first() ) === null ) {
			throw new \RuntimeException( 'No order found' );
		}

		self::$orderBaseItem = $orderBaseManager->load( self::$orderItem->getBaseId() );
	}


	protected function setUp() : void
	{
		$this->context = \TestHelperHtml::context();
		$this->emailMock = $this->getMockBuilder( '\\Aimeos\\MW\\Mail\\Message\\None' )->getMock();

		$this->view = \TestHelperHtml::view( 'unittest', $this->context->config() );

		$this->view->extAddressItem = self::$orderBaseItem->getAddress( \Aimeos\MShop\Order\Item\Base\Address\Base::TYPE_DELIVERY, 0 );
		$this->view->extOrderProductItem = self::$orderBaseItem->getProducts()->first();
		$this->view->extVoucherCode = 'test';

		$this->view->addHelper( 'mail', new \Aimeos\MW\View\Helper\Mail\Standard( $this->view, $this->emailMock ) );

		$this->object = new \Aimeos\Client\Html\Email\Voucher\Html\Standard( $this->context );
		$this->object->setView( $this->view );
	}


	protected function tearDown() : void
	{
		unset( $this->object, $this->context, $this->view );
	}


	public function testHeader()
	{
		$output = $this->object->header();
		$this->assertNotNull( $output );
	}


	public function testBody()
	{
		$ds = DIRECTORY_SEPARATOR;
		$file = '..' . $ds . 'themes' . $ds . 'default' . $ds . 'media' . $ds . 'aimeos.png';
		$this->context->config()->set( 'client/html/email/logo', $file );

		$this->emailMock->expects( $this->once() )->method( 'embed' )
			->will( $this->returnValue( 'cid:123-unique-id' ) );

		$this->emailMock->expects( $this->once() )->method( 'html' )
			->with( $this->matchesRegularExpression( '#<title>.*Your voucher.*</title>#smu' ) );

		$this->object->setView( $this->object->data( $this->view ) );
		$output = $this->object->body();

		$this->assertStringStartsWith( '<!doctype html>', $output );
		$this->assertStringContainsString( 'cid:123-unique-id', $output );

		$this->assertStringContainsString( 'email-common-intro', $output );
		$this->assertStringContainsString( 'Your voucher:', $output );

		$this->assertStringContainsString( 'email-common-body', $output );
		$this->assertStringContainsString( 'The value of your voucher', $output );

		$this->assertStringContainsString( 'email-common-outro', $output );
		$this->assertStringContainsString( 'your voucher', $output );
	}


	public function testGetSubClientInvalid()
	{
		$this->expectException( '\\Aimeos\\Client\\Html\\Exception' );
		$this->object->getSubClient( 'invalid', 'invalid' );
	}


	public function testGetSubClientInvalidName()
	{
		$this->expectException( '\\Aimeos\\Client\\Html\\Exception' );
		$this->object->getSubClient( '$$$', '$$$' );
	}
}
