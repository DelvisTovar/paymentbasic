<?php

class crdspaymentapiModuleFrontController extends ModuleFrontController {
    

    /**
     * kike api 
     */
     public function postProcess()
    {
        //Vérification générales 
        $cart = $this->context->cart;

        if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $customer =  new Customer($cart->id_customer);
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $currency = $this->context->currency;
        $total = (float)$cart->getOrderTotal(true, Cart::BOTH);


        //Traitement de la réponse OK
         if ( Tools::getValue('success') ) {
              $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_PAYMENT'), $total, $this->module->displayName, null, array(), (int)$currency->id, false, $customer->secure_key);
              Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
         } else {
             //Erreur
              $this->module->validateOrder((int)$cart->id, Configuration::get('PS_OS_ERROR'),0, $this->module->displayName, null, array(), (int)$currency->id, false, $customer->secure_key);
              Tools::redirect('index.php?controller=order&step=1');
         }
    }    
}
