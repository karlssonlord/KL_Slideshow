<?php
/**
 * Slideshow
 *
 * LICENSE
 *
 * This source file is subject to the new MIT license that is bundled
 * with this package in the file LICENSE.
 *
 * @category  KL
 * @package   KL_Slideshow
 * @author    Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright 2013 Karlsson & Lord AB
 * @license   http://opensource.org/licenses/MIT MIT License
 */

/**
 * Form block class.
 *
 * @category  KL
 * @package   KL_Slideshow
 * @author    Andreas Karlsson <andreas@karlssonlord.com>
 * @copyright 2013 Karlsson & Lord AB
 * @license   http://opensource.org/licenses/MIT MIT License
 */
class KL_Slideshow_Block_Adminhtml_Slideshow_Edit_Form
    extends Mage_Adminhtml_Block_Widget_Form
{
    /**
     * Constructor
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();

        $this->setId('slideshow_form');
        $this->setTitle(Mage::helper('slideshow')->__('Slideshow'));
    }

    /**
     * Prepare layout
     *
     * @return void
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        if (Mage::getSingleton('cms/wysiwyg_config')->isEnabled()) {
            $this->getLayout()->getBlock('head')->setCanLoadTinyMce(true);
        }
    }

    /**
     * Prepare form
     */
    protected function _prepareForm()
    {
        $model = Mage::registry('slideshow');

        $form = new Varien_Data_Form(array(
            'id'      => 'edit_form',
            'action'  => $this->getData('action'),
            'method'  => 'post',
            'enctype' => 'multipart/form-data'
        ));

        $form->setHtmlIdPrefix('slideshow_');

        $fieldset = $form->addFieldset('base_fieldset', array(
            'legend' => Mage::helper('slideshow')->__('Slideshow'),
            'class'  => 'fieldset-wide'
        ));

        if ($model->getSlideshowId()) {
            $fieldset->addField('slideshow_id', 'hidden', array(
                'name' => 'slideshow_id',
            ));
        }

        $fieldset->addField('title', 'text', array(
            'name'      => 'title',
            'label'     => Mage::helper('slideshow')->__('Title'),
            'title'     => Mage::helper('slideshow')->__('Title'),
            'required'  => true,
        ));

        if (Mage::app()->isSingleStoreMode()) {
            $fieldset->addField('store_id', 'hidden', array(
                'name'     => 'stores[]',
                'value'    => Mage::app()->getStore(true)->getId()
            ));

            $model->setStoreId(Mage::app()->getStore(true)->getId());
        } else {
            $fieldset->addField('store_id', 'multiselect', array(
                'name'     => 'stores[]',
                'label'    => Mage::helper('slideshow')->__('Store View'),
                'title'    => Mage::helper('slideshow')->__('Store View'),
                'required' => true,
                'values'   => Mage::getSingleton('adminhtml/system_store')->getStoreValuesForForm(false, true),
            ));
        }

        $fieldset->addField(
            'category_id',
            'multiselect',
            array(
                'name' => 'categories[]',
                'label' => Mage::helper('slideshow')->__('Category'),
                'title' => Mage::helper('slideshow')->__('Category'),
                'required' => false,
                'values' => Mage::helper('slideshow')->getCategories()
            )
        );

/*
        $fieldset->addField('category', 'text', array(
            'name'      => 'category',
            'label'     => Mage::helper('slideshow')->__('Category'),
            'title'     => Mage::helper('slideshow')->__('Category'),
        ));
*/
        $fieldset->addField('template', 'text', array(
            'name'      => 'template',
            'label'     => Mage::helper('slideshow')->__('Template'),
            'title'     => Mage::helper('slideshow')->__('Template'),
        ));

        $fieldset->addField('is_active', 'select', array(
            'label'    => Mage::helper('slideshow')->__('Status'),
            'title'    => Mage::helper('slideshow')->__('Status'),
            'name'     => 'is_active',
            'required' => true,
            'options'  => array(
                '1' => Mage::helper('slideshow')->__('Enabled'),
                '0' => Mage::helper('slideshow')->__('Disabled'),
            ),
        ));

        if (!$model->getId()) {
            $model->setData('is_active', '1');
        }

        $form->setValues($model->getData());
        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
