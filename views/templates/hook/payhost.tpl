{*
* 2016 Jacques Grove
*
* @author    Jacques Grove
* @copyright Jacques Grove Professional Web Development
* @version   1.0.1
* @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
* http://www.jacquesgrove.co.za
*}

<link rel="shortcut icon" type="image/x-icon" href="{$module_dir|escape:'htmlall':'UTF-8'}views/img/secure.png" />
<div class="payhost-container row">
    {if !empty($smarty.get.friendly_message|escape:'htmlall':'UTF-8')}

        <div class="modal fade" id="messagehModal" tabindex="-1" role="dialog" aria-labelledby="messagehModal" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                        <h4 class="modal-title" id="myModalLabel">{l s='There was an error:' mod='payhost'}</h4>
                    </div>
                    <div class="modal-body">
                        {l s='An error was returned from Paygate:' mod='payhost'} {$smarty.get.friendly_message|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='payhost'}</button>
                    </div>
                </div>
            </div>
        </div>

        <div id="message" style="display:none">
                <div class="modal-content">
                    <div class="modal-header">

                        <h4 class="modal-title" id="myModalLabel">{l s='There was an error:' mod='payhost'}</h4>
                    </div>
                    <div class="modal-body">
                        {l s='An error was returned from Paygate:' mod='payhost'} {$smarty.get.friendly_message|escape:'htmlall':'UTF-8'}
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default closeFancybox">{l s='Close' mod='payhost'}</button>
                    </div>
                </div>

        </div>
        <script type="text/javascript">
            $('#messagehModal').modal();
            $.fancybox({
                content: $('#message').show(),
                modal: true
            });

            $(document).on('click','.closeFancybox',function(){
                $.fancybox.close();
            })
        </script>
    {/if}
    <form name="payhost_form" id="payhost_form">
        <div class="col-lg-12 payhost-header" >
            {l s='PLEASE COMPLETE YOUR CREDIT CARD DETAILS BELOW.' mod='payhost'}
        </div>
        <div class="col-lg-2 col-md-12">
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/logoa.png" alt="Payfast Secure Payment"/>
            {if $ssl_cert != ''}
            <img src="{$module_dir|escape:'htmlall':'UTF-8'}views/img/ssl/{$ssl_cert|escape:'htmlall':'UTF-8'}.png" width="175" alt="Payfast Secure Payment"/>
            {/if}
            <ul class="card_logos">
                {if $cards.visa == 1}<li class="card_visa">Visa</li>{/if}
                {if $cards.mastercard == 1}<li class="card_mastercard">Mastercard</li>{/if}
                {if $cards.ax == 1}<li class="card_amex">American Express</li>{/if}
                {if $cards.discover == 1}<li class="card_discover">Discover</li>{/if}
                {if $cards.dinersclub == 1}<li class="card_diners">Diners Club</li>{/if}
            </ul>
        </div>
        <div class="col-lg-10 col-md-12">
            <input type="hidden" name="x_solution_ID" id="x_solution_ID" value="A1000006" />
            <input type="hidden" name="x_invoice_num" id="x_invoice_num" value="{$x_invoice_num|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="x_currency_code" id="x_currency_code" value="{$currency->iso_code|escape:'htmlall':'UTF-8'}" />
            <input type="hidden" name="cardType" id="cardType"/>
            <fieldset>
                <div class="col-xs-12 col-md-4">
                    <div class="form-group selector1">
                        <div class="col-lg-12">
                            <label for="fullname" class="payment_label">{l s='Full name' mod='payhost'}</label>
                            <input type="text" name="name" id="fullname" class="form-control grey"/>
                        </div>
                        <div class="col-lg-12">
                            <label for="x_card_num" class="payment_label">{l s='Card number' mod='payhost'}</label>
                            <input type="text" name="x_card_num" id="x_card_num" data-stripe="number" size="30" maxlength="16" class="form-control grey"/>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <label for="x_exp_date_m" class="payment_label">{l s='Expiration date' mod='payhost'}</label>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 payhost-vspace">
                            <select id="x_exp_date_m" name="x_exp_date_m" class="form-control grey">
                                {section name=date_m start=01 loop=13}
                                    <option value="{$smarty.section.date_m.index|escape:'htmlall':'UTF-8'}">{$smarty.section.date_m.index|escape:'htmlall':'UTF-8'}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <select id="x_exp_date_y" name="x_exp_date_y" class="form-control grey">
                                {section name=date_y start=16 loop=26}
                                    <option value="20{$smarty.section.date_y.index|escape:'htmlall':'UTF-8'}">20{$smarty.section.date_y.index|escape:'htmlall':'UTF-8'}</option>
                                {/section}
                            </select>
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                            <label for="x_card_code" class="payment_label">{l s='CVV' mod='payhost'}</label>
                        </div>
                        <div class="col-lg-12">
                            <input type="text" name="x_card_code" id="x_card_code" size="4" maxlength="4" class="form-control grey" />(last 3 digits on the back of your card)<br><br>
                        </div>
                        <div class="col-lg-12">
                            <button type="sumbit" id="asubmit" class="button btn btn-default standard-checkout button-medium" >
                                <span id="submitbutton">
                                    {l s='Process' mod='payhost'}
                                    <i class="icon-chevron-right right"></i>
                                </span>
                            </button>
                        </div>
                        <br class="clear" />
                    </div>
                </div>
            </fieldset>
        </div>
    </form>
</div>
<div class="modal fade" id="authModal" tabindex="-1" role="dialog" aria-labelledby="3DAuthModal" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title" id="myModalLabel">{l s='3D Authentication' mod='payhost'}</h4>
            </div>
            <div class="modal-body">
                <form id="payhostform" name="payhostform" method="post" target="payhost_redirect_frame" action="https://secure.paygate.co.za/PayHost/redirect.trans">
                    <input type="hidden" id="CHECKSUM" name="CHECKSUM">
                    <input type="hidden" id="PAY_REQUEST_ID" name="PAY_REQUEST_ID">
                    <input type="hidden" id="PAYGATE_ID" name="PAYGATE_ID">
                </form>
                <iframe id="payhost_redirect_frame" name="payhost_redirect_frame" height="550" width="100%" style="display:none;" frameborder="0" scrolling="auto"></iframe>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">{l s='Close' mod='payhost'}</button>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
    var mess_error = "{l s='Please check your credit card information (Credit card type, number and expiration date)' mod='payhost' js=1}";
    var mess_error2 = "{l s='Please specify your Full Name' mod='payhost' js=1}";
    var modules = {$modules_dir|escape:'htmlall':'UTF-8'};
    var x_invoice_num = {$x_invoice_num|escape:'htmlall':'UTF-8'}
    var field_val = '';

    {literal}
    $(document).ready(function() {

        logos_obj = '.card_logos';
        // Visa
        visa_regex = new RegExp('^4[0-9]{0,15}$');

        // MasterCard
        mastercard_regex = new RegExp('^5$|^5[1-5][0-9]{0,14}$');

        // American Express
        amex_regex = new RegExp('^3$|^3[47][0-9]{0,13}$');

        // Diners Club
        diners_regex = new RegExp('^3$|^3[068]$|^3(?:0[0-5]|[68][0-9])[0-9]{0,11}$');

        //Discover
        discover_regex = new RegExp('^6$|^6[05]$|^601[1]?$|^65[0-9][0-9]?$|^6(?:011|5[0-9]{2})[0-9]{0,12}$');

        $('#x_card_num').keyup(function(){
            cardType = '';
            cur_val = $('#x_card_num').val();
            cur_val = cur_val.replace(/ /g,'').replace(/-/g,'');
            // checks per each, as their could be multiple hits
            if ( cur_val.match(visa_regex) ) {
                $(logos_obj).addClass('is_visa');
                cardType = 'Visa';
            } else {
                $(logos_obj).removeClass('is_visa');
            }
            if ( cur_val.match(mastercard_regex) ) {
                $(logos_obj).addClass('is_mastercard');
                cardType = 'MasterCard';
            } else {
                $(logos_obj).removeClass('is_mastercard');
            }
            if ( cur_val.match(amex_regex) ) {
                $(logos_obj).addClass('is_amex');
                cardType = 'AmEx';
            } else {
                $(logos_obj).removeClass('is_amex');
            }
            if ( cur_val.match(diners_regex) ) {
                $(logos_obj).addClass('is_diners');
                cardType = 'DinersClub';
            } else {
                $(logos_obj).removeClass('is_diners');
            }
            if ( cur_val.match(discover_regex) ) {
                $(logos_obj).addClass('is_discover');
                cardType = 'Discover';
            } else {
                $(logos_obj).removeClass('is_discover');
            }

            // if nothing is a hit we add a class to fade them all out
            if ( cur_val != '' && !cur_val.match(visa_regex) && !cur_val.match(mastercard_regex)
                    && !cur_val.match(amex_regex) && !cur_val.match(diners_regex)
                    && !cur_val.match(discover_regex)) {
                $(logos_obj).addClass('is_nothing');
            } else {
                $(logos_obj).removeClass('is_nothing');
            }
        });

        $('#x_exp_date_m').children('option').each(function() {
            if ($(this).val() < 10) {
                $(this).val('0' + $(this).val());
                $(this).html($(this).val())
            }
        });

        $('#asubmit').click(function(e) {
            e.preventDefault();
            $('#submitbutton').text('Processing...');
           // $('#asubmit').prop("disabled", true);

            cardNo = $('#x_card_num').val();
            cardCode = $('#x_card_code').val();
            cardType = cardType;

            if ($('#fullname').val() == '') {
                alert(mess_error2);
            }
            else if (!validateCC(cardNo, cardType) || cardCode == '') {
                alert(mess_error);
            } else {
                var formData = {
                    'x_solution_ID'      : $('#x_solution_ID').val(),
                    'x_invoice_num'      : $('#x_invoice_num').val(),
                    'x_currency_code'    : $('#x_currency_code').val(),
                    'cardType'           : cardType,
                    'x_card_num'         : $('#x_card_num').val(),
                    'x_exp_date_m'       : $('#x_exp_date_m').val(),
                    'x_exp_date_y'       : $('#x_exp_date_y').val(),
                    'x_card_code'        : $('#x_card_code').val()
                };

                $.ajax({
                    type        : 'POST', // define the type of HTTP verb we want to use (POST for our form)
                    url         : 'modules/payhost/request.php', // the url where we want to POST
                    data        : formData, // our data object
                    dataType    : 'json', // what type of data do we expect back from the server
                    encode      : true
                })
                    // using the done promise callback
                .done(function(data) {

                    //  Handle 3Dsecure Redirect if neccessary
                    if(data.type == 'ThreeDSecure') {
                        $('#authModal').modal();
                        $('#CHECKSUM').val(data.value3);
                        $('#PAY_REQUEST_ID').val(data.value1);
                        $('#PAYGATE_ID').val(data.value2);
                        $('#abc_frame').attr('src', 'https://secure.paygate.co.za/PayHost/redirect.trans');
                        $('#payhost_redirect_frame').show();
                        $("#payhostform").submit();
                    } else if(data.type == 'Complete') {
                        durl = data.url;
                        did = data.id;
                        dcid = data.cid;
                        dao = data.ao;
                        ref = data.ref;
                        window.location = window.location.origin+'/'+durl+'&id_module='+did+'&id_cart='+dcid+'&order_reference='+ref+'&key='+dao;
                    }
                });
            }
            return false;
        });
    });{/literal}</script>
