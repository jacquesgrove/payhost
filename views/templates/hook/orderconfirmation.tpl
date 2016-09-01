{*
* 2016 Jacques Grove
*
* @author    Jacques Grove
* @copyright Jacques Grove Professional Web Development
* @version   1.0.1
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* http://www.jacquesgrove.co.za
*}

{if $status == 'ok'}
	<p style="font-size: 15px;">{l s='Success!  Thank you for shopping with us.' mod='payhost'}
		<br /><br />
        {l s='Your order number is:' mod='payhost'} {$smarty.get.order_reference|escape:'htmlall':'UTF-8'}
	</p>
{else}
	<p class="warning">
		{l s='We noticed a problem with your order. If you think this is an error, you can contact our' mod='payhost'}
		<a href="{$link->getPageLink('contact', true)|escape:'htmlall':'UTF-8'}">{l s='customer support' mod='payhost'}</a>.
	</p>
{/if}
