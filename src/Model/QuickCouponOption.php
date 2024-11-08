<?php

namespace Sunnysideup\EcommerceQuickCoupons\Model;

use SilverStripe\Control\Director;
use SilverStripe\Core\Config\Config;
use SilverStripe\Forms\LiteralField;
use SilverStripe\Forms\ReadonlyField;
use SilverStripe\Forms\Tab;
use SilverStripe\ORM\DB;
use SilverStripe\Security\Group;
use SilverStripe\Security\Member;
use SilverStripe\Security\Permission;
use SilverStripe\Security\Security;
use Sunnysideup\Ecommerce\Model\Extensions\EcommerceRole;
use Sunnysideup\EcommerceDiscountCoupon\Model\DiscountCouponOption;
use Sunnysideup\PermissionProvider\Api\PermissionProviderFactory;
use Sunnysideup\PermissionProvider\Interfaces\PermissionProviderFactoryProvider;

/**
 * Class \Sunnysideup\EcommerceQuickCoupons\Model\QuickCouponOption
 *
 * @property int $CreatedByID
 * @method \SilverStripe\Security\Member CreatedBy()
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
     */
    private static $has_one = [
        'CreatedBy' => Member::class,
    ];

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

        return $fields;
    }

    public static function permission_provider_factory_runner(): Group
    {
        $email = Config::inst()->get(QuickCouponOption::class, 'manager_email');
        $email = $email ?: self::get_default_email();

        return PermissionProviderFactory::inst()
            ->setParentGroup(EcommerceRole::get_category())
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
        $baseURL = str_replace('https://', '', (string) $baseURL);
        $baseURL = str_replace('http://', '', (string) $baseURL);
        $baseURL = trim((string) $baseURL, '/');

        return 'coupons@' . $baseURL;
    }
}
