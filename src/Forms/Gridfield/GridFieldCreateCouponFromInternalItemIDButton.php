<?php
/**
 * A modal search dialog which uses search context to search for and add
 * existing records to a grid field.
 */
class GridFieldCreateCouponFromInternalItemIDButton implements GridField_HTMLProvider, GridField_ActionProvider {

	
	/**
	 * Which template to use for rendering
	 *
	 * @var string $itemClass
	 */
	protected $itemClass = 'GridFieldCreateCouponFromInternalItemIDButton';
	
	/**
	 * @return string
	 */
	protected $title = 'Create with Product';
	
	/**
	 * @return string
	 */
	protected $targetFragment;

	/**
	 * @param string $fragment
	 */
	public function __construct($targetFragment = 'before') {
		$this->targetFragment = $targetFragment;
	}

	/**
	 * @return string
	 */
	public function getFragment() {
		return $this->targetFragment;
	}

	/**
	 * @param string $fragment
	 * @return  $this
	 */
	public function setFragment($fragment) {
		$this->targetFragment = $targetFragment;
		return $this;
	}

	public function getHTMLFragments($gridField) {
		$dataClass = $gridField->getList()->dataClass();

		$forTemplate = new ArrayData(array());
		$forTemplate->Fields = new FieldList();

		$productField = TextField::create('InternalItemID', "Product");
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
		if($form = $gridField->getForm()) {
			$forTemplate->Fields->setForm($form);
		}

		return array(

/**
  * ### @@@@ START REPLACEMENT @@@@ ###
  * WHY: automated upgrade
  * OLD: ->RenderWith( (ignore case)
  * NEW: ->RenderWith( (COMPLEX)
  * EXP: Check that the template location is still valid!
  * ### @@@@ STOP REPLACEMENT @@@@ ###
  */
			$this->targetFragment => $forTemplate->RenderWith($this->itemClass)
		);
	}

	/**
	 *
	 * @param GridField $gridField
	 * @return array
	 */
	public function getActions($gridField) {
		return ['createcoupon'];
	}

	/**
	 * Creates a coupon and adds the product (if it exists) using the value for InternalItemID 
	 *
	 * @param GridField $gridField
	 * @param string $actionName Action identifier, see {@link getActions()}.
	 * @param array $arguments Arguments relevant for this
	 * @param array $data All form data
	 */
	public function handleAction(GridField $gridField, $actionName, $arguments, $data) {
		switch($actionName) {
			case 'createcoupon':
				if(isset($data['InternalItemID']) && $data['InternalItemID']){
					$product = Product::get()->filter(['InternalItemID' => $data['InternalItemID']])->first();
					if($product){
						$validLength = Config::inst()->get(QuickCouponOption::class, 'default_valid_length_in_days');
						$newCoupon = QuickCouponOption::create();
						$newCoupon->StartDate = date('Y-m-d');
						$newCoupon->EndDate = $validLength > 0 ? date('Y-m-d', strtotime(date('Y-m-d') . $validLength . 'days')) : '';
						$newCoupon->write();
						$newCoupon->Products()->add($product);
					}
				}
				break;
		}
	}

	public function getURLHandlers($grid) {
		return [
			'create' => 'createCoupon'
		];
	}
}

