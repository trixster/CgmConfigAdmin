<?php
/**
 * CgmConfigAdmin
 *
 * @link      http://github.com/cgmartin/CgmConfigAdmin for the canonical source repository
 * @copyright Copyright (c) 2012 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/CgmConfigAdmin/master/LICENSE
 */

namespace CgmConfigAdmin\View\Helper;

use CgmConfigAdmin\Form\ConfigOptions as ConfigOptionsForm;
use Zend\View\Helper\AbstractHelper;
use Zend\Form\FieldsetInterface;
use Zend\Form\Element\Radio as RadioElement;
use Zend\Form\Element\MultiCheckbox as MultiCheckboxElement;

class CgmConfigAdminAccordionForm extends AbstractHelper
{
    /**
     * @param  ConfigOptions $form
     * @return string
     */
    public function __invoke(ConfigOptionsForm $form)
    {
        $formHelper      = $this->view->plugin('form');
        $elementHelper   = $this->view->plugin('formelement');
        $labelHelper     = $this->view->plugin('formlabel');
        $errorsHelper    = $this->view->plugin('formelementerrors');

        $output = $this->renderHeader();

        $output .= $formHelper()->openTag($form);
        $output .= $elementHelper($form->get('csrf'));

        foreach ($form as $fieldset) {
            if (! $fieldset instanceof FieldsetInterface) {
                continue;
            }

            if ($form->getNumFieldsets() > 1) {
                $output .= $this->renderSectionHeader($fieldset);
            }

            foreach ($fieldset as $element) {
                $output .= '<div class="control-group">';
                $output .= $labelHelper($element->setLabelAttributes(array('class' => 'control-label')));
                $output .= '<div class="controls">';

                $labelAttributes = array();
                if ($element instanceof RadioElement) {
                    $labelAttributes = array('class' => 'radio inline');
                } elseif ($element instanceof MultiCheckboxElement) {
                    $labelAttributes = array('class' => 'checkbox inline');
                }
                $output .= $elementHelper($element->setLabelAttributes($labelAttributes));
                $output .= $errorsHelper($element);
                $output .= '</div></div>';
            }

            if ($form->getNumFieldsets() > 1) {
                $output .= $this->renderSectionFooter($fieldset);
            }
        }

        $output .= $this->renderButtons($form);
        $output .= $formHelper()->closeTag();

        $output .= $this->renderFooter();


        return $output;
    }

    public function renderHeader()
    {
        return '<div class="accordion">';
    }

    public function renderSectionHeader(FieldsetInterface $fieldset)
    {
        $escapeHelper    = $this->view->plugin('escapehtml');
        $translateHelper = $this->view->plugin('translate');

        $output  = '<div class="accordion-group" id="' . $fieldset->getName() . '">';
        $output .= '<div class="accordion-heading">';
        $output .= '<a href="#" class="accordion-toggle" data-toggle="collapse">';
        $output .= $escapeHelper($translateHelper($fieldset->getLabel()));
        $output .= '</a></div>';
        $output .= '<div class="accordion-body collapse in"><div class="accordion-inner">';

        return $output;
    }

    public function renderSectionFooter(FieldsetInterface $fieldset)
    {
        return '</div></div></div>';
    }

    public function renderFooter()
    {
        return '</div>';
    }

    public function renderButtons(ConfigOptionsForm $form)
    {
        $elementHelper   = $this->view->plugin('formelement');

        $output = '<div class="well">';
        $output .= $elementHelper($form->get('previewBtn')->setAttribute('class', 'btn btn-primary btn-large'));
        $output .= ' ';
        $output .= $elementHelper($form->get('saveBtn')->setAttribute('class', 'btn btn-success btn-large'));
        $output .= ' ';
        $output .= $elementHelper($form->get('resetBtn')->setAttribute('class', 'btn '));
        $output .= '</div>';

        return $output;
    }
}