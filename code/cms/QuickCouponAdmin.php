<?php

class QuickCouponAdmin extends ModelAdmin
{
    /**
     * Change this variable if you want the Import from CSV form to appear.
     * This variable can be a boolean or an array.
     * If array, you can list className you want the form to appear on. i.e. array('myClassOne','myClasstwo').
     */
    public $showImportForm = false;

    /**
     * List of all managed {@link DataObject}s in this interface.
     * @var array|string
     */
    private static $managed_models = [
        QuickCouponOption::class,
    ];

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $url_segment = 'quick-coupons';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $menu_title = 'Quick Coupons';

    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $menu_icon = 'ecommerce/images/icons/money-file.gif';

    /**
     * @param int $id
     * @param FieldList $fields
     * @return Form
     */
    public function getEditForm($id = null, $fields = null)
    {
        $form = parent::getEditForm();
        if ($this->modelClass === QuickCouponOption::class) {
            if ($gridField = $form->Fields()->dataFieldByName($this->sanitiseClassName($this->modelClass))) {
                if ($gridField instanceof GridField) {
                    $gridField->getConfig()->removeComponentsByType(GridFieldExportButton::class);
                    $gridField->getConfig()->removeComponentsByType(GridFieldPrintButton::class);
                    $gridField->getConfig()->addComponent(new GridFieldCreateCouponFromInternalItemIDButton());
                }
            }
        }
        return $form;
    }
}
