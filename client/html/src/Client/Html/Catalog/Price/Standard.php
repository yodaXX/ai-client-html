<?php

/**
 * @license LGPLv3, http://opensource.org/licenses/LGPL-3.0
 * @copyright Aimeos (aimeos.org), 2020-2022
 * @package Client
 * @subpackage Html
 */


namespace Aimeos\Client\Html\Catalog\Price;


/**
 * Default implementation of catalog price HTML client
 *
 * @package Client
 * @subpackage Html
 */
class Standard
	extends \Aimeos\Client\Html\Catalog\Filter\Standard
	implements \Aimeos\Client\Html\Common\Client\Factory\Iface
{
	/** client/html/catalog/price/subparts
	 * List of HTML sub-clients rendered within the catalog price section
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
	 * @since 2018.04
	 * @category Developer
	 */
	private $subPartPath = 'client/html/catalog/price/subparts';
	private $subPartNames = ['price'];


	/**
	 * Returns the sub-client given by its name.
	 *
	 * @param string $type Name of the client type
	 * @param string|null $name Name of the sub-client (Default if null)
	 * @return \Aimeos\Client\Html\Iface Sub-client object
	 */
	public function getSubClient( string $type, string $name = null ) : \Aimeos\Client\Html\Iface
	{
		/** client/html/catalog/price/decorators/excludes
		 * Excludes decorators added by the "common" option from the catalog filter price html client
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
		 *  client/html/catalog/price/decorators/excludes = array( 'decorator1' )
		 *
		 * This would remove the decorator named "decorator1" from the list of
		 * common decorators ("\Aimeos\Client\Html\Common\Decorator\*") added via
		 * "client/html/common/decorators/default" to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2018.04
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/price/decorators/global
		 * @see client/html/catalog/price/decorators/local
		 */

		/** client/html/catalog/price/decorators/global
		 * Adds a list of globally available decorators only to the catalog filter price html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap global decorators
		 * ("\Aimeos\Client\Html\Common\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/price/decorators/global = array( 'decorator1' )
		 *
		 * This would add the decorator named "decorator1" defined by
		 * "\Aimeos\Client\Html\Common\Decorator\Decorator1" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2018.04
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/price/decorators/excludes
		 * @see client/html/catalog/price/decorators/local
		 */

		/** client/html/catalog/price/decorators/local
		 * Adds a list of local decorators only to the catalog filter price html client
		 *
		 * Decorators extend the functionality of a class by adding new aspects
		 * (e.g. log what is currently done), executing the methods of the underlying
		 * class only in certain conditions (e.g. only for logged in users) or
		 * modify what is returned to the caller.
		 *
		 * This option allows you to wrap local decorators
		 * ("\Aimeos\Client\Html\Catalog\Decorator\*") around the html client.
		 *
		 *  client/html/catalog/price/decorators/local = array( 'decorator2' )
		 *
		 * This would add the decorator named "decorator2" defined by
		 * "\Aimeos\Client\Html\Catalog\Decorator\Decorator2" only to the html client.
		 *
		 * @param array List of decorator names
		 * @since 2018.04
		 * @category Developer
		 * @see client/html/common/decorators/default
		 * @see client/html/catalog/price/decorators/excludes
		 * @see client/html/catalog/price/decorators/global
		 */

		return parent::getSubClient( $type, $name );
	}


	/**
	 * Returns the names of the subpart clients
	 *
	 * @return array List of client names
	 */
	protected function getSubClientNames() : array
	{
		return $this->context()->config()->get( $this->subPartPath, $this->subPartNames );
	}
}
