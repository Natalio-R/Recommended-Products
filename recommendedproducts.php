<?php

/**
 * 
 * @author    Natalio Rabasco <nataliorabasconavaro@gmail.com>
 * @copyright 2023 Natalio Rabasco
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License 3.0 (AFL-3.0)
 * 
 */


if (!defined('_PS_VERSION_')) {
    exit;
}

use PrestaShopBundle\Controller\Admin\FrameworkBundleAdminController as AdminController;

class RecommendedProducts extends Module
{

    public function __construct()
    {
        $this->name = 'recommendedproducts';
        $this->tab = 'front_office_features';
        $this->author = 'Natalio Rabasco';
        $this->version = '1.0.0';
        $this->need_instance = 0;

        $this->ps_versions_compliancy = [
            'min' => '1.7.1.0',
            'max' => _PS_VERSION_,
        ];

        $this->bootstrap = true;
        parent::__construct();

        $this->displayName = $this->l('Recommended products');
        $this->description = $this->l('Show featured products on the home page of your online store');
    }

    public function install()
    {
        $this->_clearCache('*'); // Clear module cache

        if (!parent::install() || $this->registerHook('displayHome') && $this->registerHook('displayRecommendedProducts')) {
            return false;
        }

        return true;
    }

    // ES - Función para eliminar el módulo en cuestión.
    // EN - Function to eliminate the module in question.
    public function uninstall()
    {
        $this->_clearCache('*'); // Clear module cache

        if (!parent::uninstall()) {
            return false;
        }

        return true;
    }


    public function getContent()
    {
        $html = '';

        // Verificar si el formulario fue enviado
        if (Tools::isSubmit('submitAddconfiguration')) {
            // Obtener los productos seleccionados
            $selectedProducts = Tools::getValue('recommendedproducts');

            if (!is_array($selectedProducts)) {
                $selectedProducts = array($selectedProducts);
            }

            // Obtener los productos existentes
            $existingProducts = explode(',', Configuration::get('RECOMMENDEDPRODUCTS'));

            // Combinar los productos existentes con los nuevos productos seleccionados
            $selectedProducts = array_unique(array_merge($existingProducts, $selectedProducts));

            // Almacenar los productos seleccionados
            Configuration::updateValue('RECOMMENDEDPRODUCTS', implode(',', $selectedProducts));
            $html .= $this->displayConfirmation($this->l('The selected products have been updated.'));
        }

        // Obtener los productos existentes
        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'name', 'ASC');

        // Obtener los productos seleccionados
        $selectedProducts = explode(',', Configuration::get('RECOMMENDEDPRODUCTS'));
        // Generar las opciones del select
        $productOptions = array();
        foreach ($products as $product) {
            $productOptions[] = array(
                'id' => $product['id_product'],
                'name' => $product['name'],
            );
        }

        // Generar el formulario
        $form = $this->generateForm($productOptions);

        //$list = $this->displayList($selectedProducts);

        $list = $this->renderList($selectedProducts);
        $html .= $form;
        $html .= $list;
        return $html;
    }

    protected function generateForm($productOptions)
    {
        // Crea una nueva instancia de HelperForm
        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        //$helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name;

        $helper->default_form_language = (int)Configuration::get('PS_LANG_DEFAULT');
        $helper->allow_employee_form_lang = (int)Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');
        $helper->title = $this->displayName;

        // Define el formulario y los campos que se mostrarán
        $fields_form = array(
            'form' => array(
                'legend' => array(
                    'title' => $this->l('Recommended products'),
                    'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'select',
                        'label' => $this->l('Select products'),
                        'name' => 'recommendedproducts[]',
                        'options' => array(
                            'query' => $productOptions,
                            'id' => 'id',
                            'name' => 'name'
                        ),
                        'multiple' => true,
                        'desc' => $this->l('Select the products you want to display on the home page.'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                    'class' => 'btn btn-default pull-right',
                ),
            ),
        );

        // Rellena los valores actuales del formulario
        $helper->fields_value['recommendedproducts'] = explode(',', Configuration::get('RECOMMENDEDPRODUCTS'));

        // Genera el formulario
        return $helper->generateForm(array($fields_form));
    }

    public function renderList($products)
    {
        $fields_list = array(
            'id_product' => array(
                'title' => $this->l('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs'
            ),
            'name' => array(
                'title' => $this->l('Name'),
            ),
            'delete' => array(
                'title' => $this->l('Delete'),
                'align' => 'center',
                'class' => 'fixed-width-sm',
                'type' => 'delete',
                'orderby' => false,
                'search' => false,
            ),
        );

        $helper = new HelperList();
        $helper->shopLinkType = '';
        $helper->simple_header = true;
        $helper->actions = array('edit', 'delete');
        $helper->identifier = 'id_product';
        $helper->table = 'product';
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $selectedProducts = $products; // Variable con ID de prueba

        $products = Product::getProducts(Context::getContext()->language->id, 0, 0, 'name', 'ASC');
        $productOptions = array();

        foreach ($products as $product) {
            if (in_array($product['id_product'], $selectedProducts)) {
                $productOptions[] = array(
                    'id_product' => $product['id_product'],
                    'name' => $product['name'],
                    'delete' => '<a href="' . $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&delete_product=' . $product['id_product'] . '&token=' . Tools::getAdminTokenLite('AdminModules') . '"><i class="icon-trash"></i></a>',
                );
            }
        }

        if (Tools::isSubmit('delete_product')) {
            $productId = (int)Tools::getValue('delete_product');
            foreach ($productOptions as $key => $productOption) {
                if ($productOption['id_product'] == $productId) {
                    unset($productOptions[$key]);
                }
            }
        }

        $helper->listTotal = count($productOptions);
        $helper->_pagination = array(20, 50, 100, 300);

        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Are you sure you want to delete the selected items?'),
                'icon' => 'icon-trash'
            )
        );

        if (Tools::isSubmit('delete_product')) {
            $productId = (int)Tools::getValue('delete_product');
            foreach ($productOptions as $key => $productOption) {
                if ($productOption['id_product'] == $productId) {
                    unset($productOptions[$key]);
                }
            }
            Configuration::updateValue('RECOMMENDEDPRODUCTS', implode(',', array_column($productOptions, 'id_product')));
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts') . '&configure=recommendedproducts&token=' . Tools::getAdminTokenLite('AdminModules'));
        }

        return $helper->generateList($productOptions, $fields_list);

        /*

        
        $helper->actions = array('edit', 'delete');
        $helper->identifier = 'id_product';
        $helper->currentIndex = AdminController::$currentIndex . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;

        $helper->listTotal = count($productOptions);
        $helper->_pagination = array(20, 50, 100, 300);

        $helper->bulk_actions = array(
            'delete' => array(
                'text' => $this->l('Delete selected'),
                'confirm' => $this->l('Are you sure you want to delete the selected items?'),
                'icon' => 'icon-trash'
            )
        );

        if (Tools::isSubmit('submitBulkdeleteproduct')) {
            $selectedProducts = Tools::getValue('productBox');
            foreach ($selectedProducts as $productId) {
                $product = new Product($productId);
                if (Validate::isLoadedObject($product)) {
                    $product->delete();
                }
            }
        }
        */
    }

    public function getActions($id_product)
    {
        $actions = array(
            array(
                'href' => $this->context->link->getAdminLink('AdminProducts') . '&id_product=' . (int)$id_product . '&action=delete&token=' . Tools::getAdminTokenLite('AdminModules'),
                'icon' => 'delete',
                'title' => $this->l('Delete'),
                'confirm' => $this->l('Are you sure you want to delete this product?')
            )
        );

        if (Tools::isSubmit('deleteproduct') && is_numeric(Tools::getValue('id_product')) && (int)Tools::getValue('id_product') === $id_product && Tools::getValue('token') == Tools::getAdminTokenLite('AdminModules')) {
            $product = new Product($id_product);
            if (Validate::isLoadedObject($product)) {
                $product->delete();
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminProducts') . '&configure=recommendedproducts&token=' . Tools::getAdminTokenLite('AdminModules'));
            }
        }

        return $actions;
    }

    public function hookDisplayRecommendedProducts($selectedProducts)
    {
        $selectedProducts = Configuration::get('RECOMMENDEDPRODUCTS');
        $productIds = explode(',', $selectedProducts);
        $products = array();
        foreach ($productIds as $productId) {
            $product = new Product($productId);
            if (Validate::isLoadedObject($product)) {
                $products[] = $product;
            }
        }
        $this->context->smarty->assign(array(
            'products' => $products,
        ));
        return $this->display(__FILE__, 'views/templates/hook/recommended_products.tpl');
    }
}
