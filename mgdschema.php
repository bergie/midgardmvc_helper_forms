<?php
/**
 * @package midgardmvc_helper_forms
 * @author The Midgard Project, http://www.midgard-project.org
 * @copyright The Midgard Project, http://www.midgard-project.org
 * @license http://www.gnu.org/licenses/lgpl.html GNU Lesser General Public License
 */
class midgardmvc_helper_forms_mgdschema
{
    public static function create(midgard_object $object)
    {
        $form_namespace = get_class($object);
        if ($object->guid)
        {
            $form_namespace = $object->guid;
        }

        $form = midgardmvc_helper_forms::create($form_namespace);
        midgardmvc_helper_forms_mgdschema::object_to_form($object, $form);
        return $form;
    }

    public static function object_to_form($object, midgardmvc_helper_forms_group $form)
    {
        // Go through object properties
        $props = get_object_vars($object);
        $reflectionproperty = new midgard_reflection_property(get_class($object));
        foreach ($props as $property => $value)
        {
            if (   $property == 'action'
                || $property == 'guid'
                || $property == 'id')
            {
                // TODO: Make the list of properties to not render configurable
                continue;
            }
            
            if ($property == 'metadata')
            {
                // Metadata is to be handled as a group
                $metadata = $form->add_group('metadata');
                $metadata->set_label('metadata');
                midgardmvc_helper_forms_mgdschema::object_to_form($value, $metadata);
            }
            
            $type = $reflectionproperty->get_midgard_type($property);
            switch ($type)
            {
                case MGD_TYPE_STRING:
                    $field = $form->add_field($property, 'text');
                    $field->set_value($value);
                    $widget = $field->set_widget('text');
                    $widget->set_label($property);
                    $widget->set_placeholder($reflectionproperty->description($property));
                    // TODO: maxlength to 255
                    break;
                case MGD_TYPE_LONGTEXT:
                    if ($reflectionproperty->get_user_value($property, 'contenttype') == 'html')
                    {
                        $field = $form->add_field($property, 'html');
                        $widget = $field->set_widget('html');
                    }
                    else
                    {
                        $field = $form->add_field($property, 'text');
                        $widget = $field->set_widget('textarea');
                    }
                    $field->set_value($value);
                    $widget->set_label($property);
                    $widget->set_placeholder($reflectionproperty->description($property));
                    break;
                case MGD_TYPE_INT:
                    $field = $form->add_field($property, 'integer');
                    $field->set_value($value);
                    $widget = $field->set_widget('number');
                    $widget->set_label($property);
                    $widget->set_placeholder($reflectionproperty->description($property));
                    break;
                case MGD_TYPE_UINT:
                    $field = $form->add_field($property, 'integer');
                    $field->set_value($value);
                    // TODO: Set minimum value to 0
                    $widget = $field->set_widget('number');
                    $widget->set_label($property);
                    $widget->set_placeholder($reflectionproperty->description($property));
                    break;
                case MGD_TYPE_BOOLEAN:
                    $field = $form->add_field($property, 'boolean');
                    $field->set_value($value);
                    break;
                case MGD_TYPE_FLOAT:
                case MGD_TYPE_TIMESTAMP:
                case MGD_TYPE_GUID:
                    break;
            }
        }
    }

    public static function form_to_object(midgardmvc_helper_forms_group $form, $object)
    {
        // Go through form items and fill the object
        $items = $form->items;
        foreach ($items as $key => $item)
        {
            if (!property_exists($object, $key))
            {
                // The object has no such property
                continue;
            }
            
            if (   $item instanceof midgardmvc_helper_forms_group
                && $key == 'metadata')
            {
                midgardmvc_helper_forms_mgdschema::form_to_object($item, $object->metadata);
                continue;
            }
            
            $object->$key = $item->get_value();
        }
    }   
}
