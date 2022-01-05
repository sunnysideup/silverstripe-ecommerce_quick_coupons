<?php

namespace Sunnysideup\EcommerceQuickCoupons\Model;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Sunnysideup\EcommerceDiscountCoupon\Model\DiscountCouponOption;
use Sunnysideup\PermissionProvider\Api\PermissionProviderFactory;
use Sunnysideup\PermissionProvider\Interfaces\PermissionProviderFactoryProvider;

/**
 *@author nicolaas [at] sunnysideup.co.nz
 *@author shane [at] sunnysideup.co.nz
 */
class QuickCouponOption extends DiscountCouponOption implements PermissionProviderFactoryProvider
{
    /**
     * standard SS variable.
     *
     * @var string
     */
    private static $table_name = 'QuickCouponOption';

    /**
     * standard SS variable.
     *
     * @var array
     */
    private static $defaults = [
        'ApplyPercentageToApplicableProducts' => true,
        'NumberOfTimesCouponCanBeUsed' => 1,
    ];

    /**
     * standard SS variable.
     */
    private static $has_one = [
        'CreatedBy' => Member::class,
    ];

    /**
     * standard SS variable.
     */
    private static $searchable_fields = [
        'Title' => 'PartialMatchFilter',
        'Code' => 'PartialMatchFilter',
        'DiscountAbsolute' => 'ExactMatchFilter',
        'DiscountPercentage' => 'ExactMatchFilter',
    ];

    /**
     *  default number of days that a coupon will be valid for
     *  used to set value of EndDate in getCMSFields
     *  set to -1 to disable.
     *
     *  @var int
     */
    private static $default_valid_length_in_days = 7;

    /**
     *  @var string
     */
    private static $manager_email = '';

    /**
     * standard SS method.
     *
     * @param null|mixed $member
     * @param mixed      $context
     *
     * @return bool
     */
    public function canCreate($member = null, $context = [])
    {
        if (Permission::checkMember($member, 'CMS_ACCESS_QUICK_COUPONS')) {
            return true;
        }

        return parent::canCreate($member);
    }

    /**
     * standard SS method.
     *
     * @param null|mixed $member
     * @param mixed      $context
     *
     * @return bool
     */
    public function canView($member = null, $context = [])
    {
        if (Permission::checkMember($member, 'CMS_ACCESS_QUICK_COUPONS')) {
            return true;
        }

        return parent::canView($member);
    }

    /**
     * standard SS method.
     *
     * @param null|mixed $member
     * @param mixed      $context
     *
     * @return bool
     */
    public function canEdit($member = null, $context = [])
    {
        if (Permission::checkMember($member, 'CMS_ACCESS_QUICK_COUPONS')) {
            return true;
        }

        return parent::canEdit($member);
    }

    /**
     * standard SS method.
     *
     * @param null|mixed $member
     *
     * @return bool
     */
    public function canDelete($member = null)
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
     * standard SS method.
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
            $member = Member::get_by_id($this->AddedByID);
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
    public static function permission_provider_factory_runner()
    {
        $email = Config::inst()->get(QuickCouponOption::class, 'manager_email');
        $email = $email ?: self::get_default_email();

        return PermissionProviderFactory::inst()
            ->setEmail($email)
            ->setFirstName('Coupon')
            ->setSurname('Manager')
            ->setGroupName('Coupon Managers')
            ->setCode('couponmanagers')
            ->setPermissionCode('CMS_ACCESS_QUICK_COUPONS')
            ->setRoleTitle('Coupon Manager Privileges')
            ->setPermissionArray(['CMS_ACCESS_QuickCouponAdmin'])
            ->CreateGroupAndMember()
        ;
    }

    /**
     * standard SS method.
     */
    public function requireDefaultRecords()
    {
        parent::requireDefaultRecords();
        DB::alteration_message('Creating Coupon Manager Group', 'created');
    }

    /**
     * standard SS method.
     */
    protected function onBeforeWrite()
    {
        parent::onBeforeWrite();
        if (! $this->CreatedByID) {
            $currentMember = Security::getCurrentUser();
            $this->CreatedByID = $currentMember->ID;
        }
    }

    /**
     * Returns and email address based on the current domain of this website.
     *
     * @return string
     */
    private static function get_default_email()
    {
        $baseURL = Director::absoluteBaseURL();
        $baseURL = str_replace('https://', '', $baseURL);
        $baseURL = str_replace('http://', '', $baseURL);
        $baseURL = trim($baseURL, '/');

        return 'coupons@' . $baseURL;
    }
}
