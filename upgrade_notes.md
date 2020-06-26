2020-06-26 12:06

# running php upgrade upgrade see: https://github.com/silverstripe/silverstripe-upgrader
cd /var/www/upgrades/ecommerce_quick_coupons
php /var/www/upgrader/vendor/silverstripe/upgrader/bin/upgrade-code upgrade /var/www/upgrades/ecommerce_quick_coupons/ecommerce_quick_coupons  --root-dir=/var/www/upgrades/ecommerce_quick_coupons --write -vvv
Writing changes for 3 files
Running upgrades on "/var/www/upgrades/ecommerce_quick_coupons/ecommerce_quick_coupons"
[2020-06-26 12:06:13] Applying RenameClasses to QuickCouponAdmin.php...
[2020-06-26 12:06:13] Applying ClassToTraitRule to QuickCouponAdmin.php...
[2020-06-26 12:06:13] Applying RenameClasses to GridFieldCreateCouponFromInternalItemIDButton.php...
[2020-06-26 12:06:13] Applying ClassToTraitRule to GridFieldCreateCouponFromInternalItemIDButton.php...
[2020-06-26 12:06:13] Applying RenameClasses to QuickCouponOption.php...
[2020-06-26 12:06:13] Applying ClassToTraitRule to QuickCouponOption.php...
[2020-06-26 12:06:13] Applying RenameClasses to _config.php...
[2020-06-26 12:06:13] Applying ClassToTraitRule to _config.php...
modified:	src/Cms/QuickCouponAdmin.php
@@ -2,12 +2,19 @@

 namespace Sunnysideup\EcommerceQuickCoupons\Cms;

-use ModelAdmin;
-use QuickCouponOption;
-use GridField;
-use GridFieldExportButton;
-use GridFieldPrintButton;
-use GridFieldCreateCouponFromInternalItemIDButton;
+
+
+
+
+
+
+use Sunnysideup\EcommerceQuickCoupons\Model\QuickCouponOption;
+use SilverStripe\Forms\GridField\GridField;
+use SilverStripe\Forms\GridField\GridFieldExportButton;
+use SilverStripe\Forms\GridField\GridFieldPrintButton;
+use Sunnysideup\EcommerceQuickCoupons\Forms\Gridfield\GridFieldCreateCouponFromInternalItemIDButton;
+use SilverStripe\Admin\ModelAdmin;
+


 class QuickCouponAdmin extends ModelAdmin

modified:	src/Forms/Gridfield/GridFieldCreateCouponFromInternalItemIDButton.php
@@ -2,16 +2,28 @@

 namespace Sunnysideup\EcommerceQuickCoupons\Forms\Gridfield;

-use GridField_HTMLProvider;
-use GridField_ActionProvider;
-use ArrayData;
-use FieldList;
-use TextField;
-use GridField_FormAction;
-use GridField;
-use Product;
-use Config;
-use QuickCouponOption;
+
+
+
+
+
+
+
+
+
+
+use Sunnysideup\EcommerceQuickCoupons\Forms\Gridfield\GridFieldCreateCouponFromInternalItemIDButton;
+use SilverStripe\View\ArrayData;
+use SilverStripe\Forms\FieldList;
+use Sunnysideup\Ecommerce\Pages\Product;
+use SilverStripe\Forms\TextField;
+use SilverStripe\Forms\GridField\GridField_FormAction;
+use SilverStripe\Forms\GridField\GridField;
+use SilverStripe\Core\Config\Config;
+use Sunnysideup\EcommerceQuickCoupons\Model\QuickCouponOption;
+use SilverStripe\Forms\GridField\GridField_HTMLProvider;
+use SilverStripe\Forms\GridField\GridField_ActionProvider;
+

 /**
  * A modal search dialog which uses search context to search for and add
@@ -25,7 +37,7 @@
 	 *
 	 * @var string $itemClass
 	 */
-	protected $itemClass = 'GridFieldCreateCouponFromInternalItemIDButton';
+	protected $itemClass = GridFieldCreateCouponFromInternalItemIDButton::class;

 	/**
 	 * @return string
@@ -66,7 +78,7 @@
 		$forTemplate = new ArrayData(array());
 		$forTemplate->Fields = new FieldList();

-		$productField = TextField::create('InternalItemID', "Product");
+		$productField = TextField::create('InternalItemID', Product::class);
 		$productField->setAttribute('placeholder', 'Internal Item ID');
 		$productField->setAttribute('required', true);


modified:	src/Model/QuickCouponOption.php
@@ -2,15 +2,25 @@

 namespace Sunnysideup\EcommerceQuickCoupons\Model;

-use DiscountCouponOption;
-use Member;
-use Permission;
-use Config;
-use LiteralField;
-use Tab;
-use DB;
-use PermissionProviderFactory;
-use Director;
+
+
+
+
+
+
+
+
+
+use SilverStripe\Security\Member;
+use SilverStripe\Security\Permission;
+use SilverStripe\Core\Config\Config;
+use SilverStripe\Forms\LiteralField;
+use SilverStripe\Forms\Tab;
+use SilverStripe\ORM\DB;
+use Sunnysideup\PermissionProvider\Api\PermissionProviderFactory;
+use SilverStripe\Control\Director;
+use Sunnysideup\EcommerceDiscountCoupon\Model\DiscountCouponOption;
+


 /**

Writing changes for 3 files
✔✔✔