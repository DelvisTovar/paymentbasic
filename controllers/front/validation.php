<?php
class PrestapayValidationModuleFrontController extends ModuleFrontController
{
    public function postProcess()
    {

        $cart = $this->context->cart;
        $authorized = false;
 
        if (!$this->module->active || $cart->id_customer == 0 || $cart->id_address_delivery == 0
            || $cart->id_address_invoice == 0) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        foreach (Module::getPaymentModules() as $module) {
            if ($module['name'] == 'prestapay') {
                $authorized = true;
                break;
            }
        }
 
        if (!$authorized) {
            die($this->l('This payment method is not available.'));
        }
 
        $customer = new Customer($cart->id_customer);
 
        if (!Validate::isLoadedObject($customer)) {
            Tools::redirect('index.php?controller=order&step=1');
        }

        $this->module->validateOrder(
            (int) $this->context->cart->id,
            Configuration::get('PS_OS_PAYMENT'),
            (float) $this->context->cart->getOrderTotal(true, Cart::BOTH),
            $this->module->displayName,
            null,
            null,
            (int) $this->context->currency->id,
            false,
            $customer->secure_key
        );
 
        Tools::redirect('index.php?controller=order-confirmation&id_cart='.(int)$cart->id.'&id_module='.(int)$this->module->id.'&id_order='.$this->module->currentOrder.'&key='.$customer->secure_key);
    }
}