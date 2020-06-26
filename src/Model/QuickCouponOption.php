<?php

namespace Sunnysideup\EcommerceQuickCoupons\Model;










use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DB;
use Sunnysideup\PermissionProvider\Api\PermissionProviderFactory;
use SilverStripe\Control\Director;
use Sunnysideup\EcommerceDiscountCoupon\Model\DiscountCouponOption;



/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@author shane [at] sunnysideup.co.nz
 *
 **/
class QuickCouponOption extends DiscountCouponOption
{
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $defaults = [
        'ApplyPercentageToApplicableProducts' => true,
        'NumberOfTimesCouponCanBeUsed' => 1,
    ];

    /**
     * standard SS variable
     */
    private static $has_one = [
        'CreatedBy' => Member::class,
    ];

     /**
     * standard SS variable
     *
     */
    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        "Code" => "PartialMatchFilter",
        'DiscountAbsolute' => 'ExactMatchFilter',
        'DiscountPercentage' => 'ExactMatchFilter'
    ];

    /**
     *  default number of days that a coupon will be valid for
     *  used to set value of EndDate in getCMSFields
     *  set to -1 to disable
     *  @var int
     */
    private static $default_valid_length_in_days = 7;

    /**
     *  @var string
     */
    private static $manager_email = '';

    /**
     * standard SS method
     * @param Member $member | NULL
     * @return boolean
     */
    public function canCreate($member = null, $context = [])
    {
        if (Permission::checkMember($member, 'CMS_ACCESS_QUICK_COUPONS')) {
            return true;
        }
        return parent::canCreate($member);
    }

    /**
     * standard SS method
     * @param Member $member | NULL
     * @return boolean
     */
    public function canView($member = null, $context = [])
    {
        if (Permission::checkMember($member, 'CMS_ACCESS_QUICK_COUPONS')) {
            return true;
        }
        return parent::canView($member);
    }

    /**
     * standard SS method
     * @param Member $member | NULL
     * @return boolean
     */
    public function canEdit($member = null, $context = [])
    {
        if (Permission::checkMember($member, 'CMS_ACCESS_QUICK_COUPONS')) {
            return true;
        }
        return parent::canEdit($member);
    }

    /**
     * standard SS method
     *
     * @param Member $member | NULL
     *
     * @return boolean
     */
    public function canDelete($member = null, $context = [])
    {
        if ($this->UseCount()) {
            return false;
        }
        if (Permission::checkMember($member, 'CMS_ACCESS_QUICK_COUPONS')) {
            return true;
        }
        return parent::canDelete($member);
    }

    /**
     * standard SS method
     */
    public function getCMSFields()
    {
        $fields = parent::getCMSFields();
        $fields->removeByName('CreatedByID');
        $fields->removeByName('ApplyEvenWithoutCode');
        $fields->removeByName('AddProductsUsingCategories');

        if (! $this->StartDate) {
            $fields->dataFieldByName('StartDate')->setValue(date('Y-m-d'));
        }

        $validLength = Config::inst()->get(QuickCouponOption::class, 'default_valid_length_in_days');
        if (! $this->EndDate && $validLength >= 0) {
            $fields->dataFieldByName('EndDate')->setValue(
                date('Y-m-d', strtotime(date('Y-m-d') . $validLength . 'days'))
            );
        }

        if ($this->AddedByID) {
            $member = Member::get()->byID($this->AddedByID);
            if ($member && $member->exists()) {
                $fields->insertBefore(
                    'UseCount',
                    LiteralField::create(
                        'CreatedByReadOnly',
                        '<div class="field readonly">
	                        <label class="left">Created By</label>
	                        <div class="middleColumn">
                            	<span class="readonly">
                                    ' . $member->Name . '
                                </span>
                        	</div>
                        </div>'
                    )
                );
            }
        }

        $fields->insertBefore(
            new Tab('Discount', 'Discount'),
            'AddProductsDirectly'
        );

        $fields->addFieldsToTab(
            'Root.Discount',
            [
                $fields->dataFieldByName('MaximumDiscount'),
                $fields->dataFieldByName('DiscountAbsolute'),
                $fields->dataFieldByName('DiscountPercentage'),
                $fields->dataFieldByName('MinimumOrderSubTotalValue'),
            ]
        );

        return $fields;
    }

    /**
     * standard SS method
     */
    public function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->CreatedByID) {
            $currentMember = Member::currentUser();
            $this->CreatedByID = $currentMember->ID;
        }
    }

    /**
     * standard SS method
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        DB::alteration_message('Creating Coupon Manager Group', 'created');
        $email = Config::inst()->get(QuickCouponOption::class, 'manager_email');
        $email = $email ?: $this->getDefaultEmail();
        $group = PermissionProviderFactory::inst()
            ->setEmail($email)
            ->setFirstName('Coupon')
            ->setSurname('Manager')
            ->setName('Coupon Managers')
            ->setCode('couponmanagers')
            ->setPermissionCode('CMS_ACCESS_QUICK_COUPONS')
            ->setRoleTitle('Coupon Manager Privileges')
            ->setPermissionArray(['CMS_ACCESS_QuickCouponAdmin'])
            ->CreateGroupAndMember();
    }

    /**
     * Returns and email address based on the current domain of this website
     *
     * @return string
     */
    private function getDefaultEmail()
    {
        $baseURL = Director::absoluteBaseURL();
        $baseURL = str_replace('https://', '', $baseURL);
        $baseURL = str_replace('http://', '', $baseURL);
        $baseURL = trim($baseURL, '/');
        return 'coupons@' . $baseURL;
    }
}

