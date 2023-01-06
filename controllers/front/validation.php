<?php
require_once _PS_MODULE_DIR_.'/crdsaplazamiento/classes/crdsAplazamientoObj.php';

class PaymentBasicvalidationModuleFrontController extends ModuleFrontController
{

    /**
     * Validation du paiement standard
     * Puis redirection vers la page de succès de commande
     */
    public function postProcess()
    {
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer = new Customer($cart->id_customer);

        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);

        //La command passe directement en statut payé
        $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, null, array(), (int)$currency->id, false, $customer->secure_key);

        $idCart = crdsAplazamientoObj::getAplazamientoByIdCart((int)$cart->id);
        if($idCart){
            $aplazamientoAddOrder = crdsAplazamientoObj::addAplazamientoIdOrder((int)$cart->id, $this->module->currentOrder);
        }

        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }

}