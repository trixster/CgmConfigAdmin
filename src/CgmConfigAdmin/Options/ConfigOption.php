<?php
/**
 * CgmConfigAdmin
 *
 * @link      http://github.com/cgmartin/CgmConfigAdmin for the canonical source repository
 * @copyright Copyright (c) 2012 Christopher Martin (http://cgmartin.com)
 * @license   New BSD License https://raw.github.com/cgmartin/CgmConfigAdmin/master/LICENSE
 */
namespace CgmConfigAdmin\Options;

use Zend\Stdlib\AbstractOptions;
use Zend\Form\Factory as FormFactory;

class ConfigOption extends AbstractOptions
{
    protected static $elementMappings = array(
        'radio'         => 'Zend\Form\Element\Radio',
        'select'        => 'Zend\Form\Element\Select',
        'multicheckbox' => 'Zend\Form\Element\MultiCheckbox',
        'text'          => 'Zend\Form\Element\Text',
        'number'        => 'Zend\Form\Element\Number',
    );

    /**
     * Turn off strict options mode
     */
    protected $__strictMode__ = false;

    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $inputType = 'radio';

    /**
     * @var array
     */
    protected $valueOptions;

    /**
     * @var string|array
     */
    protected $defaultValue;

    /**
     * @var string
     */
    protected $group = 'default';


    /**
     * @static
     * @param array $mappings
     */
    public static function setElementMappings(array $mappings)
    {
        self::$elementMappings = $mappings;
    }

    /**
     * @static
     * @return array
     */
    public static function getElementMappings()
    {
        return self::$elementMappings;
    }

    /**
     * @param string                $id
     * @param null|int|string|array $options
     */
    public function __construct($id, $options = null)
    {
        $this->setId($id);
        if (is_bool($options)) {
            $this->setInputType('radio');
            $this->setDefaultValue(($options) ? '1' : '');
        } elseif (is_numeric($options)) {
            $this->setInputType('number');
            $this->setDefaultValue($options);
        } elseif (is_string($options)) {
            $this->setInputType('text');
            $this->setDefaultValue($options);
        } elseif (is_array($options) && !$this->isAssocArray($options)) {
            $this->setInputType('select');
            $this->setValueOptions($options);
        } else {
            parent::__construct($options);
        }
    }

    /**
     * @param $arr
     * @return bool
     */
    protected function isAssocArray($arr)
    {
        return (bool)count(array_filter(array_keys($arr), 'is_string'));
    }

    /**
     * @param  string $id
     * @return ConfigOption
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param  string $label
     * @return ConfigOption
     */
    public function setLabel($label)
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        if (!isset($this->label) && isset($this->id)) {
            // Convert camelCase or dash-string or under_score ids to a
            // human readable label for convenience
            $label = preg_replace(
                array(
                    '/(?<=[^A-Z])([A-Z])/',
                    '/(?<=[^0-9])([0-9])/',
                ),
                ' $0',
                $this->id
            );
            $label = preg_replace_callback(
                '/[\-_]([a-zA-Z0-9])/',
                function ($matches) {
                    return ' ' . strtoupper($matches[1]);
                },
                $label
            );
            $this->label = ucwords($label);
        }
        return $this->label;
    }

    /**
     * @param string $inputType
     * @return ConfigOption
     */
    public function setInputType($inputType)
    {
        $this->inputType = $inputType;
        return $this;
    }

    /**
     * @return string
     */
    public function getInputType()
    {
        return $this->inputType;
    }

    /**
     * @param array $valueOptions
     * @return ConfigOption
     */
    public function setValueOptions($valueOptions)
    {
        $this->valueOptions = $valueOptions;
        return $this;
    }

    /**
     * @return array
     */
    public function getValueOptions()
    {
        return $this->valueOptions;
    }

    /**
     * @param array|string $defaultValue
     * @return ConfigOption
     */
    public function setDefaultValue($defaultValue)
    {
        $this->defaultValue = $defaultValue;
        return $this;
    }

    /**
     * @return array|string
     */
    public function getDefaultValue()
    {
        return $this->defaultValue;
    }

    /**
     * @param string $group
     * @return ConfigOption
     */
    public function setGroup($group)
    {
        $this->group = $group;
        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * Create a form element spec from internal data
     *
     * @return array
     */
    public function createFormElementSpec()
    {
        $elementSpec = array();

        $type = $this->getInputType();
        $elementSpec['type'] = (array_key_exists($type, self::$elementMappings))
            ? self::$elementMappings[$type] : $type;

        $elementSpec['name'] = $this->getId();
        $elementSpec['options']['label'] = $this->getLabel();

        // Default Value
        if (null !== ($defaultValue = $this->getDefaultValue())) {
            $elementSpec['attributes']['value'] = $defaultValue;
        }

        // Value Options
        if (null !== ($valueOptions = $this->getValueOptions())) {
            if (!$this->isAssocArray($valueOptions)) {
                $valueOptions = array_combine($valueOptions, $valueOptions);
            }
            $elementSpec['options']['value_options'] = $valueOptions;

            if (null === $defaultValue) {
                reset($valueOptions);
                $elementSpec['attributes']['value'] = key($valueOptions);
            }

        } elseif ('radio' === $type) {
            $elementSpec['options']['value_options'] = array(
                '1' => 'Yes', '' => 'No',
            );
            if (null === $defaultValue) {
                $elementSpec['attributes']['value'] = '';
            }
        }

        return $elementSpec;
    }
}