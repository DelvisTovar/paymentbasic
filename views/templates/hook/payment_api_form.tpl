<form method="post" action="{$payment_url}">
    <div class="form-group">
        {* choix du mode de carte *}
        {l s='please choose your card type' mod='paymentbasic'}
            <div class="radio">
                <label>
                    <input type="radio" name="cb_type" value="BFC" id="cb_type1" checked="checked" />  BFC Delvis
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="cb_type" id="cb_type2" value="Venezuela"/> Venezuela Delvis
                </label>
            </div>
            <div class="radio">
                <label>
                    <input type="radio" name="cb_type" id="cb_type3" value="Bicentenario"/> Bicentenario Delvis
                </label>
            </div>
    </div>
    {* Informations pour l'api *}
    <input type="hidden" name="success_url" value="{$success_url}" />
    <input type="hidden" name="error_url" value="{$error_url}" />
    <input type="hidden" name="id_cart" value="{$id_cart}" />
    <input type="hidden" name="cart_total" value="{$cart_total}" />
    <input type="hidden" name="id_customer" value="{$id_customer}" />
</form>