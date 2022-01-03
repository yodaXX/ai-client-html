<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Metaways Infosystems GmbH, 2014
 * @copyright Aimeos (aimeos.org), 2015-2022
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Account\Favorite;


/**
 * Default implementation of account favorite HTML client.
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Common\Client\Factory\Base
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/account/favorite/subparts
	 * List of HTML sub-clients rendered within the account favorite section
	 *
	 * The output of the frontend is composed of the code generated by the HTML
	 * clients. Each HTML client can consist of serveral (or none) sub-clients
	 * that are responsible for rendering certain sub-parts of the output. The
	 * sub-clients can contain HTML clients themselves and therefore a
	 * hierarchical tree of HTML clients is composed. Each HTML client creates
	 * the output that is placed inside the container of its parent.
	 *
	 * At first, always the HTML code generated by the parent is printed, then
	 * the HTML code of its sub-clients. The order of the HTML sub-clients
	 * determines the order of the output of these sub-clients inside the parent
	 * container. If the configured list of clients is
	 *
	 *  array( "subclient1", "subclient2" )
	 *
	 * you can easily change the order of the output by reordering the subparts:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1", "subclient2" )
	 *
	 * You can also remove one or more parts if they shouldn't be rendered:
	 *
	 *  client/html/<clients>/subparts = array( "subclient1" )
	 *
	 * As the clients only generates structural HTML, the layout defined via CSS
	 * should support adding, removing or reordering content by a fluid like
	 * design.
	 *
	 * @param array List of sub-client names
	 * @since 2014.03
	 * @category Developer
	 */
	private $subPartPath = 'client/html/account/favorite/subparts';
	private $subPartNames = [];
	private $view;


	/**
	 * Returns the HTML code for insertion into the body.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string HTML code
	 */
	public function body( string $uid = '' ) : string
	{
		$context = $this->context();
		$view = $this->view();

		try
		{
			$view = $this->view = $this->view ?? $this->object()->data( $view );

			$html = '';
			foreach( $this->getSubClients() as $subclient ) {
				$html .= $subclient->setView( $view )->body( $uid );
			}
			$view->favoriteBody = $html;
		}
		catch( \Aimeos\Client\Html\Exception $e )
		{
			$error = array( $context->translate( 'client', $e->getMessage() ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
		}
		catch( \Aimeos\Controller\Frontend\Exception $e )
		{
			$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( $context->translate( 'mshop', $e->getMessage() ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
		}
		catch( \Exception $e )
		{
			$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
			$this->logException( $e );
		}

		/** client/html/account/favorite/template-body
		 * Relative path to the HTML body template of the account favorite client.
		 *
		 * The template file contains the HTML code and processing instructions
		 * to generate the result shown in the body of the frontend. The
		 * configuration string is the path to the template file relative
		 * to the templates directory (usually in client/html/templates).
		 *
		 * You can overwrite the template file configuration in extensions and
		 * provide alternative templates. These alternative templates should be
		 * named like the default one but with the string "standard" replaced by
		 * an unique name. You may use the name of your project for this. If
		 * you've implemented an alternative client class as well, "standard"
		 * should be replaced by the name of the new class.
		 *
		 * @param string Relative path to the template creating code for the HTML page body
		 * @since 2014.03
		 * @category Developer
		 * @see client/html/account/favorite/template-header
		 */
		$tplconf = 'client/html/account/favorite/template-body';
		$default = 'account/favorite/body-standard';

		return $view->render( $view->config( $tplconf, $default ) );
	}


	/**
	 * Returns the HTML string for insertion into the header.
	 *
	 * @param string $uid Unique identifier for the output if the content is placed more than once on the same page
	 * @return string|null String including HTML tags for the header on error
	 */
	public function header( string $uid = '' ) : ?string
	{
		$view = $this->view();

		try
		{
			$view = $this->view = $this->view ?? $this->object()->data( $view );

			$html = '';
			foreach( $this->getSubClients() as $subclient ) {
				$html .= $subclient->setView( $view )->header( $uid );
			}
			$view->favoriteHeader = $html;

			/** client/html/account/favorite/template-header
			 * Relative path to the HTML header template of the account favorite client.
			 *
			 * The template file contains the HTML code and processing instructions
			 * to generate the HTML code that is inserted into the HTML page header
			 * of the rendered page in the frontend. The configuration string is the
			 * path to the template file relative to the templates directory (usually
			 * in client/html/templates).
			 *
			 * You can overwrite the template file configuration in extensions and
			 * provide alternative templates. These alternative templates should be
			 * named like the default one but with the string "standard" replaced by
			 * an unique name. You may use the name of your project for this. If
			 * you've implemented an alternative client class as well, "standard"
			 * should be replaced by the name of the new class.
			 *
			 * @param string Relative path to the template creating code for the HTML page head
			 * @since 2014.03
			 * @category Developer
			 * @see client/html/account/favorite/template-body
			 */
			$tplconf = 'client/html/account/favorite/template-header';
			$default = 'account/favorite/header-standard';

			return $view->render( $view->config( $tplconf, $default ) );
		}
		catch( \Exception $e )
		{
			$this->logException( $e );
		}

		return null;
	}


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Client\Html\Iface Sub-client object
	 */
	public function getSubClient( string $type, string $name = null ) : \Aimeos\Client\Html\Iface
	{
		/** client/html/account/favorite/decorators/excludes
		 * Excludes decorators added by the "common" option from the account favorite html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to remove a decorator added via
		 * "client/html/common/decorators/default" before they are wrapped
		 * around the html client.
		 *
		 *  client/html/account/favorite/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/account/favorite/decorators/global
		 * @see client/html/account/favorite/decorators/local
		 */

		/** client/html/account/favorite/decorators/global
		 * Adds a list of globally available decorators only to the account favorite html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/account/favorite/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/account/favorite/decorators/excludes
		 * @see client/html/account/favorite/decorators/local
		 */

		/** client/html/account/favorite/decorators/local
		 * Adds a list of local decorators only to the account favorite html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Account\Decorator\*") around the html client.
		 *
		 *  client/html/account/favorite/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Account\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2014.05
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/account/favorite/decorators/excludes
		 * @see client/html/account/favorite/decorators/global
		 */
		return $this->createSubClient( 'account/favorite/' . $type, $name );
	}


	/**
	 * Processes the input, e.g. store given values.
	 *
	 * A view must be available and this method doesn't generate any output
	 * besides setting view variables if necessary.
	 */
	public function init()
	{
		$view = $this->view();
		$context = $this->context();
		$ids = (array) $view->param( 'fav_id', [] );

		try
		{
			if( $context->user() !== null && !empty( $ids ) && $view->request()->getMethod() === 'POST' )
			{
				switch( $view->param( 'fav_action' ) )
				{
					case 'add':
						$this->addFavorites( $ids ); break;
					case 'delete':
						$this->deleteFavorites( $ids ); break;
				}
			}

			parent::init();
		}
		catch( \Aimeos\MShop\Exception $e )
		{
			$error = array( $context->translate( 'mshop', $e->getMessage() ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
		}
		catch( \Aimeos\Controller\Frontend\Exception $e )
		{
			$error = array( $context->translate( 'controller/frontend', $e->getMessage() ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
		}
		catch( \Aimeos\Client\Html\Exception $e )
		{
			$error = array( $context->translate( 'client', $e->getMessage() ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
		}
		catch( \Exception $e )
		{
			$error = array( $context->translate( 'client', 'A non-recoverable error occured' ) );
			$view->favoriteErrorList = array_merge( $view->get( 'favoriteErrorList', [] ), $error );
			$this->logException( $e );
		}
	}


	/**
	 * Adds new product favorite references to the given customer
	 *
	 * @param array $ids List of product IDs
	 */
	protected function addFavorites( array $ids )
	{
		$context = $this->context();

		/** client/html/account/favorite/maxitems
		 * Maximum number of products that can be favorites
		 *
		 * This option limits the number of products users can add to their
		 * favorite list. It must be a positive integer value greater than 0.
		 *
		 * @param integer Number of products
		 * @since 2019.04
		 * @category User
		 * @category Developer
		 */
		$max = $context->config()->get( 'client/html/account/favorite/maxitems', 100 );

		$cntl = \Aimeos\Controller\Frontend::create( $context, 'customer' );
		$item = $cntl->uses( ['product' => ['favorite']] )->get();

		if( count( $item->getRefItems( 'product', null, 'favorite' ) ) + count( $ids ) > $max )
		{
			$msg = sprintf( $context->translate( 'client', 'You can only save up to %1$s products as favorites' ), $max );
			throw new \Aimeos\Client\Html\Exception( $msg );
		}

		foreach( $ids as $id )
		{
			if( ( $listItem = $item->getListItem( 'product', 'favorite', $id ) ) === null ) {
				$listItem = $cntl->createListItem();
			}
			$cntl->addListItem( 'product', $listItem->setType( 'favorite' )->setRefId( $id ) );
		}

		$cntl->store();
	}


	/**
	 * Removes product favorite references from the customer
	 *
	 * @param array $ids List of product IDs
	 */
	protected function deleteFavorites( array $ids )
	{
		$cntl = \Aimeos\Controller\Frontend::create( $this->context(), 'customer' );
		$item = $cntl->uses( ['product' => ['favorite']] )->get();

		foreach( $ids as $id )
		{
			if( ( $listItem = $item->getListItem( 'product', 'favorite', $id ) ) !== null ) {
				$cntl->deleteListItem( 'product', $listItem );
			}
		}

		$cntl->store();
	}


	/**
	 * Returns the list of sub-client names configured for the client.
	 *
	 * @return array List of HTML client names
	 */
	protected function getSubClientNames() : array
	{
		return $this->context()->config()->get( $this->subPartPath, $this->subPartNames );
	}


	/**
	 * Returns the sanitized page from the parameters for the product list.
	 *
	 * @param \Aimeos\MW\View\Iface $view View instance with helper for retrieving the required parameters
	 * @return int Page number starting from 1
	 */
	protected function getProductListPage( \Aimeos\MW\View\Iface $view ) : int
	{
		$page = (int) $view->param( 'fav_page', 1 );
		return ( $page < 1 ? 1 : $page );
	}


	/**
	 * Returns the sanitized page size from the parameters for the product list.
	 *
	 * @param \Aimeos\MW\View\Iface $view View instance with helper for retrieving the required parameters
	 * @return int Page size
	 */
	protected function getProductListSize( \Aimeos\MW\View\Iface $view ) : int
	{
		/** client/html/account/favorite/size
		 * The number of products shown in a list page for favorite products
		 *
		 * Limits the number of products that is shown in the list pages to the
		 * given value. If more products are available, the products are split
		 * into bunches which will be shown on their own list page. The user is
		 * able to move to the next page (or previous one if it's not the first)
		 * to display the next (or previous) products.
		 *
		 * The value must be an integer number from 1 to 100. Negative values as
		 * well as values above 100 are not allowed. The value can be overwritten
		 * per request if the "l_size" parameter is part of the URL.
		 *
		 * @param integer Number of products
		 * @since 2014.09
		 * @category User
		 * @category Developer
		 * @see client/html/catalog/lists/size
		 */
		$defaultSize = $this->context()->config()->get( 'client/html/account/favorite/size', 48 );

		$size = (int) $view->param( 'fav-size', $defaultSize );
		return ( $size < 1 || $size > 100 ? $defaultSize : $size );
	}


	/**
	 * Sets the necessary parameter values in the view.
	 *
	 * @param \Aimeos\MW\View\Iface $view The view object which generates the HTML output
	 * @param array &$tags Result array for the list of tags that are associated to the output
	 * @param string|null &$expire Result variable for the expiration date of the output (null for no expiry)
	 * @return \Aimeos\MW\View\Iface Modified view object
	 */
	public function data( \Aimeos\MW\View\Iface $view, array &$tags = [], string &$expire = null ) : \Aimeos\MW\View\Iface
	{
		$context = $this->context();

		/** client/html/account/favorite/domains
		 * A list of domain names whose items should be available in the account favorite view template
		 *
		 * The templates rendering product details usually add the images,
		 * prices and texts associated to the product item. If you want to
		 * display additional or less content, you can configure your own
		 * list of domains (attribute, media, price, product, text, etc. are
		 * domains) whose items are fetched from the storage. Please keep
		 * in mind that the more domains you add to the configuration, the
		 * more time is required for fetching the content!
		 *
		 * @param array List of domain names
		 * @since 2014.09
		 * @category Developer
		 * @see client/html/catalog/domains
		 */
		$domains = $context->config()->get( 'client/html/account/favorite/domains', ['text', 'price', 'media'] );
		$domains['product'] = ['favorite'];

		$cntl = \Aimeos\Controller\Frontend::create( $context, 'customer' );
		$listItems = $cntl->uses( $domains )->get()->getListItems( 'product', 'favorite' );
		$total = count( $listItems );

		$size = $this->getProductListSize( $view );
		$current = $this->getProductListPage( $view );
		$last = ( $total != 0 ? ceil( $total / $size ) : 1 );

		$view->favoriteItems = $listItems;
		$view->favoritePageFirst = 1;
		$view->favoritePagePrev = ( $current > 1 ? $current - 1 : 1 );
		$view->favoritePageNext = ( $current < $last ? $current + 1 : $last );
		$view->favoritePageLast = $last;
		$view->favoritePageCurr = $current;

		return parent::data( $view, $tags, $expire );
	}
}
