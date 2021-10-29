<?php

namespace Sunnysideup\EcommerceQuickCoupons\Forms\Gridfield;

use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\FieldList;
use SilverStripe\Forms\GridField\GridField;
use SilverStripe\Forms\GridField\GridField_ActionProvider;
use SilverStripe\Forms\GridField\GridField_FormAction;
use SilverStripe\Forms\GridField\GridField_HTMLProvider;
use SilverStripe\Forms\TextField;
use SilverStripe\View\ArrayData;
use Sunnysideup\Ecommerce\Pages\Product;
use Sunnysideup\EcommerceQuickCoupons\Model\QuickCouponOption;

/**
 * A modal search dialog which uses search context to search for and add
 * existing records to a grid field.
 */
class GridFieldCreateCouponFromInternalItemIDButton implements GridField_HTMLProvider, GridField_ActionProvider
{
    /**
     * Which template to use for rendering.
     *
     * @var string
     */
    protected $itemClass = GridFieldCreateCouponFromInternalItemIDButton::class;

    /**
     * @return string
     */
    protected $title = 'Create with Product';

    /**
     * @return string
     */
    protected $targetFragment;

    /**
     * @param string $targetFragment
     */
    public function __construct($targetFragment = 'before')
    {
        $this->targetFragment = $targetFragment;
    }

    /**
     * @return string
     */
    public function getFragment()
    {
        return $this->targetFragment;
    }

    /**
     * @param string $fragment
     *
     * @return $this
     */
    public function setFragment($fragment)
    {
        $this->targetFragment = $fragment;

        return $this;
    }

    public function getHTMLFragments($gridField)
    {
        $forTemplate = new ArrayData([]);
        $forTemplate->Fields = new FieldList();

        $productField = TextField::create('InternalItemID', Product::class);
        $productField->setAttribute('placeholder', 'Internal Item ID');
        $productField->setAttribute('required', true);

        $addAction = new GridField_FormAction(
            $gridField,
            'gridfield_relationadd',
            $this->title,
            'createcoupon',
            'createcoupon'
        );

        $forTemplate->Fields->push($productField);
        $forTemplate->Fields->push($addAction);

        $form = $gridField->getForm();
        if ($form) {
            $forTemplate->Fields->setForm($form);
        }

        return [
            $this->targetFragment => $forTemplate->RenderWith($this->itemClass),
        ];
    }

    /*Sunnysideup\EcommerceQuickCoupons\Forms\Gridfield
     * @param GridField $gridField
     * @return array
     */
    public function getActions($gridField)
    {
        return ['createcoupon'];
    }

    /**
     * Creates a coupon and adds the product (if it exists) using the value for InternalItemID.
     *
     * @param string $actionName action identifier, see {@link getActions()}
     * @param array  $arguments  Arguments relevant for this
     * @param array  $data       All form data
     */
    public function handleAction(GridField $gridField, $actionName, $arguments, $data)
    {
        if ('createcoupon' === $actionName) {
            if (isset($data['InternalItemID']) && $data['InternalItemID']) {
                $product = Product::get()->filter(['InternalItemID' => $data['InternalItemID']])->first();
                if ($product) {
                    $validLength = Config::inst()->get(QuickCouponOption::class, 'default_valid_length_in_days');
                    $newCoupon = QuickCouponOption::create();
                    $newCoupon->StartDate = date('Y-m-d');
                    $newCoupon->EndDate = $validLength > 0 ? date('Y-m-d', strtotime(date('Y-m-d') . $validLength . 'days')) : '';
                    $newCoupon->write();
                    $newCoupon->Products()->add($product);
                }
            }
        }
    }

    public function getURLHandlers($grid)
    {
        return [
            'create' => 'createCoupon',
        ];
    }
}
