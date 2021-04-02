<?php
if (!defined('_PS_VERSION_')) {
    exit;
}
 
class PrestaPay extends PaymentModule
{
    private $_html = '';
    private $_postErrors = array();
    public $address;

    public function __construct()
    {
        $this->name                   = 'prestapay';
        $this->tab                    = 'payments_gateways';
        $this->version                = '1.0';
        $this->author                 = 'Andresa Martins';
        $this->controllers            = array('payment', 'validation');
        $this->currencies             = true;
        $this->currencies_mode        = 'checkbox';
        $this->bootstrap              = true;
        $this->displayName            = $this->l('PrestaPay');
        $this->description            = $this->l('Sample Payment module developed for learning purposes.');
        $this->confirmUninstall       = $this->l('Are you sure you want to uninstall this module?');
        $this->ps_versions_compliancy = array('min' => '1.7.0', 'max' => _PS_VERSION_);
 
        parent::__construct();
    }
 
    public function install()
    {
        return parent::install()
            && $this->registerHook('paymentOptions')
            && $this->registerHook('paymentReturn');
    }
 
    public function uninstall()
    {
        return parent::uninstall();
    }
 
    public function getContent()
    {
        return $this->_html;
    }

    public function hookPaymentOptions($params)
    {
        if (!$this->active) {
            return;
        }
 
        $formAction = $this->context->link->getModuleLink($this->name, 'validation', array(), true);
        $this->smarty->assign(['action' => $formAction]);
        $paymentForm = $this->fetch('module:prestapay/views/templates/hook/payment_options.tpl');

        $newOption = new PrestaShop\PrestaShop\Core\Payment\PaymentOption;
        $newOption->setModuleName($this->displayName)
            ->setCallToActionText($this->displayName)
            ->setAction($formAction)
            ->setForm($paymentForm);
 
        $payment_options = array(
            $newOption
        );
 
        return $payment_options;
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active) {
            return;
        }
 
        return $this->fetch('module:prestapay/views/templates/hook/payment_return.tpl');
    }
}
