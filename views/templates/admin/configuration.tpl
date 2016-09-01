{*
* 2016 Jacques Grove
*
* @author    Jacques Grove
* @copyright Jacques Grove Professional Web Development
* @version   1.0.1
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* http://www.jacquesgrove.co.za
*}
<div class="payhost-wrapper">
<a href="https://www.paygate.co.za" class="payhost-logo" target="_blank"><img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/paygate-logo.png" alt="Paygate Payhost" border="0" /></a>
<p class="payhost-intro">{l s='Start accepting payments through your PrestaShop store with Paygate Payhost.' mod='payhost'}</p>
<br>
    <form action="{$smarty.server.REQUEST_URI|escape:'htmlall':'UTF-8'}" method="post">
    <fieldset>
        <legend>{l s='Configure your existing Paygate Payhost Account' mod='payhost'}</legend>
        <table>
            <tr>
                <td>
                    <p>{l s='Live Credentials for' mod='payhost'}<b> {$currency.iso_code|escape:'htmlall':'UTF-8'}</b> {l s='currency' mod='payhost'}</p>
                    <label for="payhost_live_login_id">{l s='Live Login ID' mod='payhost'}:</label>
                    <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="payhost_live_login_id" name="payhost_live_login_id" value="{$PAYHOST_LIVE_LOGIN_ID|escape:'htmlall':'UTF-8'}" /></div>
                    <label for="payhost_live_key">{l s='Live Key' mod='payhost'}:</label>
                    <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="payhost_live_key" name="payhost_live_key" value="{$PAYHOST_LIVE_KEY|escape:'htmlall':'UTF-8'}" /></div>
                </td>
            </tr>
        </table>
        <br>
        <table>
            <tr>
                <td>
                    <p>{l s='Test Credentials for' mod='payhost'}<b> {$currency.iso_code|escape:'htmlall':'UTF-8'}</b> {l s='currency' mod='payhost'}</p>
                    <label for="payhost_test_login_id">{l s='Test Login ID' mod='payhost'}:</label>
                    <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="payhost_test_login_id" name="payhost_test_login_id" value="{$PAYHOST_TEST_LOGIN_ID|escape:'htmlall':'UTF-8'}" /></div>
                    <label for="payhost_test_key">{l s='Test Key' mod='payhost'}:</label>
                    <div class="margin-form" style="margin-bottom: 0px;"><input type="text" size="40" id="payhost_test_key" name="payhost_test_key" value="{$PAYHOST_TEST_KEY|escape:'htmlall':'UTF-8'}" /></div>
                </td>
            </tr>
        </table>
        <br />
        <hr style="border: 0; height: 1px; background: #ccc;" />
        <br />
        <label for="payhost_mode"> {l s='Environment:' mod='payhost'}</label>
        <div class="margin-form" id="payhost_mode">
            <input type="radio" name="payhost_mode" value="0" style="vertical-align: middle;" {if !$PAYHOST_MODE}checked="checked"{/if} />
            <span>{l s='Live mode' mod='payhost'}</span><br/>
            <input type="radio" name="payhost_mode" value="1" style="vertical-align: middle;" {if $PAYHOST_MODE}checked="checked"{/if} />
            <span>{l s='Test mode' mod='payhost'}</span><br/>
        </div>
        <br />
        <hr style="border: 0; height: 1px; background: #ccc;" />
        <br />
        <label for="payhost_debug"> {l s='Debug:' mod='payhost'}</label>
        <div class="margin-form" id="payhost_debug">
            <input type="checkbox" name="payhost_debug" style="vertical-align: middle;" {if $PAYHOST_DEBUG}checked="checked"{/if} />
            <span>{l s='Debug mode (write transaction entries to the log file)' mod='payhost'}</span><br/>
        </div>
        <br />
        <hr style="border: 0; height: 1px; background: #ccc;" />
        <br />
        <label for="payhost_cards">{l s='Enabled Cards :' mod='payhost'}</label>
        <div class="margin-form" id="payhost_cards">
            <input type="checkbox" name="payhost_card_visa" {if $PAYHOST_CARD_VISA}checked="checked"{/if} />
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cards/visa.gif" alt="Vias" />
            <input type="checkbox" name="payhost_card_mastercard" {if $PAYHOST_CARD_MASTERCARD}checked="checked"{/if} />
                <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cards/mastercard.gif" alt="Mastercard" />
            <input type="checkbox" name="payhost_card_discover" {if $PAYHOST_CARD_DISCOVER}checked="checked"{/if} />
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cards/discover.gif" alt="Discover" />
            <input type="checkbox" name="payhost_card_ax" {if $PAYHOST_CARD_AX}checked="checked"{/if} />
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cards/ax.gif" alt="American Express" />
            <input type="checkbox" name="payhost_card_dinersclub" {if $PAYHOST_CARD_DINERSCLUB}checked="checked"{/if} />
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/cards/diners.gif" alt="Diner's Club International" />
        </div>
        <br />
        <hr style="border: 0; height: 1px; background: #ccc;" />
        <br />
        <label for="payhost_ssl_cert">{l s='SSL Provider :' mod='payhost'}</label>
        <div class="margin-form" >
            <select name="payhost_ssl_cert">
                <option value="" {if $PAYHOST_SSL_CERT|escape:'htmlall':'UTF-8' == ''}selected{/if}>No SSL</option>
                <option value="certum" {if $PAYHOST_SSL_CERT|escape:'htmlall':'UTF-8' == 'certum'}selected{/if}>Certum</option>
                <option value="comodo" {if $PAYHOST_SSL_CERT|escape:'htmlall':'UTF-8' == 'comodo'}selected{/if}>Comodo</option>
                <option value="geotrust" {if $PAYHOST_SSL_CERT|escape:'htmlall':'UTF-8' == 'geotrust'}selected{/if}>Geotrust</option>
                <option value="rapidssl" {if $PAYHOST_SSL_CERT|escape:'htmlall':'UTF-8' == 'rapidssl'}selected{/if}>Rapid SSL</option>
                <option value="symantec" {if $PAYHOST_SSL_CERT|escape:'htmlall':'UTF-8' == 'symantec'}selected{/if}>Symantec</option>
                <option value="thawt" {if $PAYHOST_SSL_CERT|escape:'htmlall':'UTF-8' == 'thawt'}selected{/if}>Thawte</option>
            </select>
        </div>
        <br />
        <hr style="border: 0; height: 1px; background: #ccc;" />
        <br />
        <center>
            <input type="submit" name="submitModule" value="{l s='Update settings' mod='payhost'}" class="button" />
        </center>
    </fieldset>
</form>
</div>
