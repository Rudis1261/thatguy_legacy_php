<?php

    # Author: Rudi Strydom
    # Date: Aug 2013
    # Puspose: I am sick of having to code out forms. And the classes I have seen are bloated and does not suit my needs.
    #          So here we go, my own bootstrap version

    class Form
    {
        //public $htmlTags            = array();
        //public $xmlTags             = array();
        public $form_attributes     = array();
        public $form_children       = array();
        public $Error;
        public $validation_errors   = 0;
        public $submit              = false;

        # How does it all begin?
        function __construct( $overide_defaults=false ) {

            # We need some basic form attributes to start off
            $this->form_attributes = array(
                "method"        => "post",
                "role"          => "form"
            );

            # We may also want to overide the detauls on construct
            if ($overide_defaults) {

                # The override them
                $this->attributes((array)$overide_defaults);
            }

            # Initiate the error class
            $this->Error = new Error();
        }

        # We may want to be able to alter or add some attributes
        function attributes($array_input=false) {

            # Ensure it is an array
            if (is_array($array_input))
            {
                # Cool it's an array.
                foreach($array_input as $attribute => $value)
                {
                    $this->form_attributes[$attribute] = $value;
                }
                return true;
            }
            return false;
        }

        # Which method is being used?
        function method($overide_method=false)
        {
            # Check whether the method is to be overidden, and whether it is valid
            if (($overide_method) AND ((strtlower($overide_method) == "get") OR (strtlower($overide_method) == "post")))
            {
                # Valid, so overide it
                $this->form_attributes["method"] = strtolower($overide_method);
            }

            # Then we should return the method for the user so they know which method it is
            return $this->form_attributes["method"];
        }


        # Add more elements (Children)
        function add($attributes) {

            $this->form_children[]  = $attributes;
        }

        # Add more elements (Children)
        function addInput($attributes) {

            $attributes["tag"]          = "input";
            $attributes["class"]        = (!empty($attributes["class"]))    ? ($attributes["class"])    : "form-control";
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addTextarea($attributes) {

            $attributes["tag"]          = "textarea";
            $attributes["autocomplete"] = "off";
            $attributes["class"]        = (!empty($attributes["class"]))    ? ($attributes["class"])    : "form-control";
            $attributes["rows"]         = (!empty($attributes["rows"]))     ? ($attributes["rows"])     : 5;
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addPassword($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "password";
            $attributes["autocomplete"] = "off";
            $attributes["class"]        = (!empty($attributes["class"]))    ? ($attributes["class"])    : "form-control";
            $this->form_children[]   = $attributes;
        }

        # Add more elements (Children)
        function addHidden($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "hidden";
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addCheckbox($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "checkbox";
            $attributes["class"]        = (!empty($attributes["class"]))    ? ($attributes["class"])    : "form-control";

            # Checkboxes work a bit differently, we need to ensure that the form does indeed have a name parameter
            if (!empty($attributes["name"]) AND !empty($_REQUEST[$attributes["name"]]))
            {
                $attributes["checked"]  = "";
            }
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addText($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "text";
            $attributes["autocomplete"] = "off";
            $attributes["class"]        = (!empty($attributes["class"]))    ? ($attributes["class"])    : "form-control";
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addDatePicker($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "text";
            $attributes["autocomplete"] = "off";
            $attributes['data-date-format'] = "dd/mm/yyyy";
            $attributes["class"]        = (!empty($attributes["class"]))            ? ($attributes["class"])            : "form-control datepicker2";
            $attributes["icon"]         = (!empty($attributes["icon"]))             ? ($attributes["icon"])             : "glyphicon glyphicon-calendar";
            $attributes["value"]        = (!empty($attributes["value"]))            ? ($attributes["value"])            : date("d/m/Y", time());
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addFile($attributes) {
            $attributes["tag"]          = "button";
            $attributes["class"]        = "btn btn-default btn-inverse btn-lg";
            $attributes["onClick"]      = "open_new_window('uploader.php', 800, 600); return false;";
            $attributes["value"]        = "Upload Documents & Images " . icon('upload', true);
            $attributes["title"]        = "NOTE: Should the uploader not display, ensure to allow the popup";
            $this->form_children[]      = $attributes;
        }

        # Alias of addFile
        function addUpload($attributes) {
            addFile($attributes);
        }

        # Add more elements (Children)
        function addTimePicker($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "text";
            $attributes["autocomplete"] = "off";
            $attributes["append-class"] = "bootstrap-timepicker";
            $attributes["class"]        = (!empty($attributes["class"]))            ? ($attributes["class"])            : "timepicker form-control";
            $attributes["icon"]         = (!empty($attributes["icon"]))             ? ($attributes["icon"])             : "glyphicon glyphicon-time";
            $attributes["value"]        = (!empty($attributes["value"]))            ? ($attributes["value"])            : date("H:i", time());
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addSubmit($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "submit";
            $attributes["class"]        = (!empty($attributes["class"]))    ? ($attributes["class"])    : "btn btn-default btn-primary";
            $attributes["value"]        = (!empty($attributes["value"]))    ? ($attributes["value"])    : "Submit";
            $attributes["name"]         = (!empty($attributes["name"]))     ? ($attributes["name"])     : "submit";
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addReset($attributes) {

            $attributes["tag"]          = "input";
            $attributes["type"]         = "reset";
            $attributes["class"]        = (!empty($attributes["class"]))    ? ($attributes["class"])    : "btn btn-default btn-danger";
            $attributes["value"]        = (!empty($attributes["value"]))    ? ($attributes["value"])    : "Reset";
            $attributes["name"]         = (!empty($attributes["name"]))     ? ($attributes["name"])     : "reset";
            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addSelect($options, $attributes) {

            $attributes["tag"]          = "select";
            $attributes["class"]        = (!empty($attributes["class"]))    ? $attributes["class"]          : "form-control";
            $attributes["name"]         = (!empty($attributes["name"]))     ? $attributes["name"]           : false;
            $attributes["value"]        = (!empty($attributes["value"]))    ? (array)$attributes["value"]   : null;
            $attributes["size"]         = (!empty($attributes["size"]))     ? $attributes["size"]           : null;
            $attributes["options"]      = (array)$options;

            # Check if the size is set, which will imply a multi select
            if (($attributes["size"]) AND (!key_exists("multiple", $attributes)))
            {
                $attributes["multiple"] = "";
            }

            # The selects are a bit special to be honest:
            $multi_select               = (key_exists("multiple", $attributes)) ? true                      : false;
            $array_format               = (strstr($attributes["name"], "[]"))   ? true                      : false;

            # Let's check whether the name is set and whether it is set in the array format or not
            if ($multi_select AND !$array_format AND $attributes["name"]) {
                $attributes["name"] = $attributes["name"] . "[]";
            }

            # Let's also check the sire of the display block
            if ($multi_select AND !$attributes["size"])
            {
                $attributes["size"] = 5;
            }

            $this->form_children[]      = $attributes;
        }

        # Add more elements (Children)
        function addBool($attributes, $options=array("no", "yes")) {

            $attributes["tag"]          = "select";
            $attributes["class"]        = (!empty($attributes["class"]))    ? $attributes["class"]          : "form-control input input-sm";
            $attributes["value"]        = (!empty($attributes["value"]))    ? (array)$attributes["value"]   : "true";
            $attributes["options"]      = (array)$options;
            $this->form_children[]      = $attributes;
        }

        # We may want to be able to add custom code
        function addCustom($html_to_add=false)
        {
            # Check that we have been supplied with some html
            if ($html_to_add)
            {
                # Then add the custom HTML
                $this->form_children[] = array("tag"=>"custom", "value"=> $html_to_add);
            }
            return false;
        }


        # We may also want to add a class
        function addClass($classes_to_add=false) {

            # Ensure it is an array
            if ($classes_to_add)
            {
                # Explode by space
                $classes = explode(' ', $this->form_attributes['class']);

                # Loop through the new additions
                foreach( (array)$classes_to_add as $class_to_add)
                {
                    $class_to_add = strtolower($class_to_add);

                    # Ensure that the section does not already exists
                    if (!in_array($class_to_add, $classes))
                    {
                        # Then append the option and implode the array
                        $classes[] = $class_to_add;
                    }
                }

                # Only do the implode once
                $this->form_attributes['class'] = implode(" ", $classes);
                return true;
            }
            return false;
        }



        # We may also want to remove a class
        function removeClass($classes_to_remove=false) {

            # Ensure it is an array
            if ($classes_to_remove)
            {
                # Explode by space
                $classes = explode(' ', $this->form_attributes['class']);

                # Loop through the things we want removed
                foreach( (array)$classes_to_remove as $class_to_remove)
                {
                    $class_to_remove = strtolower($class_to_remove);

                    # Loop throug the classes
                    foreach($classes as $index => $class)
                    {
                        # If the class is indeed found then unset it from the exploded array
                        if ($class_to_remove == $class)
                        {
                            unset($classes[$index]);
                        }
                    }
                }

                # Only do the implode once
                $this->form_attributes['class'] = implode(" ", $classes);
                return true;
            }
            return false;
        }



        # This function will be used to implode an array into an HTML format
        function implode_attributes($array_input=false) {

            # Ensure it is an array
            if (is_array($array_input))
            {
                # We need a string to put this all toghether
                $output = " ";

                # Cool it's an array.
                foreach($array_input as $attribute => $value)
                {
                    # Drop the tag
                    if ($attribute == "tag") continue;

                    # If it's empty
                    if (empty($value))
                    {
                        # Then it's solely an attribute
                        $output .= $attribute . ' ';

                    } else {

                        # append the Attribute value pair
                        $output .= $attribute . '="' . $value .'" ';
                    }
                }

                # Return the output
                return $output;
            }
            # Otherwise return the string as is
            return $array_input;
        }


        # We should also be able to validate the input the user has provided
        function validate($label_or_name, $value, $validation) {

            //$validation_error
            $valid = true;

            # Check if it's an array
            if (is_array($validation))  {

                # Then loop through the
                foreach( $validation as $type=>$level) {

                    //var_dump(preg_match('/[a-z].*/ism',$value));

                    # What type of validation will we be doing?
                    switch($type)
                    {
                        # Let's check the length of a string
                        case "long":
                        case "len":
                        case "length":
                            if (strlen($value) < $level) {
                                $this->Error->add("error", "<b>" . ucfirst($label_or_name) . "</b> is too short");
                                $valid = false;
                                $this->validation_errors = $this->validation_errors + 1;
                            }
                            break;

                        # What about string or number
                        case "num":
                        case "number":
                        case "int":
                            if (!is_numeric($value))
                            {
                                $this->Error->add("error", "<b>" . ucfirst($label_or_name) . "</b> needs to be a number");
                                $valid = false;
                                $this->validation_errors = $this->validation_errors + 1;
                            }
                            break;

                        # Let's check if it's a string or not
                        case "string":
                        case "letter":
                        case "character":
                            if (preg_match('/[\d].*/', $value))
                            {
                                $this->Error->add("error", "<b>" . ucfirst($label_or_name) . "</b> needs to be a string");
                                $valid = false;
                                $this->validation_errors = $this->validation_errors + 1;
                            }
                            break;

                        # Let's check if it's a string or not
                        case "empty":
                        case "blank":
                            if (empty($value))
                            {
                                $this->Error->add("error", "<b>" . ucfirst($label_or_name) . "</b> is empty, please be sure ti fill it in");
                                $valid = false;
                                $this->validation_errors = $this->validation_errors + 1;
                            }
                            break;

                        # Let's validate an email as well
                        case "email":
                            if (!$this->validateEmail($value))
                            {
                                $this->Error->add("error", "<b>" . ucfirst($label_or_name) . "</b> contains an invalid email address");
                                $valid = false;
                                $this->validation_errors = $this->validation_errors + 1;
                            }
                            break;

                        default:
                            break;
                    }
                }
            }
            return $valid;
        }


        // Is an email address valid?
        function validateEmail($val) {

            // Preg match as well as do a MX lookup
            if (preg_match("/^([_a-z0-9+-]+)(\.[_a-z0-9-]+)*@([a-z0-9-]+)(\.[a-z0-9-]+)*(\.[a-z]{2,4})$/i", $val)) {

                // Check if it's a linux box and do a MX Record
                if (strtoupper(substr(PHP_OS, 0, 3)) !== 'WIN') {

                    list( , $domain) = explode("@", $val);
                    return getmxrr($domain, $mxrecords);

                } else {

                    return true;
                }
            }
            return false;
        }


        # Should the validation be done, then we should be able to see that it is
        function valid() {

            # Ensure that the form has been submitted before
            if ($this->submit)
            {
                # Check that there are not errors
                if ($this->validation_errors == 0) {

                    return true;
                }
            }
            return false;
        }

        # Return whether the form has been submitted or not yet
        function submitted() {

            return $this->submit;
        }


        # This function will end off the form and return it for output
        function render() {

            # Add a hidden form so we can see if it has been submitted or not
            $this->addHidden( array("name"=>"formzy_submit_run", "value"=>"true") );
            if (isset($_REQUEST["formzy_submit_run"])) {

                $this->submit = true;
            } else {
                $this->submit = false;
            }

            # Build up the form
            $output = "<form " . $this->implode_attributes( $this->form_attributes ) . ">";

            # If there are children then loop through them
            if (!empty($this->form_children))
            {
                foreach($this->form_children as $child)
                {
                    # Check that there is a name for the input, otherwise we are not able to retrieve the value for it
                    if (!empty($child["name"]))
                    {
                        $name = (str_replace("[]", "", $child["name"]));

                        if (!empty($_REQUEST[$name]))
                        {
                            $child["value"] = $_REQUEST[$name];
                        }
                    }

                    # We need some set variables regardless of what type of html tag it is
                    $label              = (!empty($child["label"]))         ? '<label class="control-label">' . ucfirst( $child["label"] ) . '</label>'    : "";
                    $error              = (!empty($child["error"]))         ? ' error '                                                                    : "";
                    $value              = (!empty($child["value"]))         ? $child["value"]                                                              : "";
                    $tag                = (!empty($child["tag"]))           ? $child["tag"]                                                                : "";
                    $options            = (!empty($child["options"]))       ? $child["options"]                                                            : "";
                    $validation         = (!empty($child["validation"]))    ? $child["validation"]                                                         : false;
                    $name               = (!empty($child["name"]))          ? $child["name"]                                                               : "";
                    $icon               = (!empty($child["icon"]))          ? $child["icon"]                                                               : "";
                    $appendClass        = (!empty($child["append-class"]))  ? $child["append-class"]                                                       : "";

                    if ($validation AND (!empty($name)) AND $this->submit)
                    {
                        $name_or_label = (!empty($child["label"])) ? $child["label"] : $name;
                        $validate = $this->validate($name_or_label, $value, $validation);
                        $error = (!$validate) ? " error " : "";
                    }

                    # We may also want to remove some details from being generated
                    if (!empty($child["tag"]))          unset($child["tag"]);
                    if (!empty($child["options"]))      unset($child["options"]);
                    if (!empty($child["validation"]))   unset($child["validation"]);
                    if (!empty($child["icon"]))         unset($child["icon"]);
                    if (!empty($child["group-class"]))  unset($child["group-class"]);

                    # Check the inputs
                    if ($tag == "input") {

                        # Create the input line
                        $input = '<' . $tag . $this->implode_attributes($child) . '/>';

                        # We need to ensure that it's not a hidden field
                        if ($child["type"] !== "hidden") {

                            # These outputs require the special classing
                            $output .= '<div class="form-group ' . $error . '">
                                            ' . $label;

                            # If there is an icon then append it
                            if (!empty($icon))
                            {
                                $output .= '<div class="input-append ' . $appendClass . '">
                                                ' . $input . '
                                                <span class="add-on"><i class="icon ' . $icon . '"></i></span>
                                            </div>';

                            # Otherwise just append it
                            } else {

                                $output .= $input;
                            }

                            $output .=  '</div>';
                        }

                        # What if it is a hidden field?
                        elseif ($child["type"] == "hidden") {

                            # Then we only have the hidden input
                            $output .= $input;
                        }
                    }


                    # What is it's a textarea?
                    if (($tag == "textarea") OR ($tag == "button")) {

                        # REMOVE THE VALUE TAG FROM THE LIST
                        if ($value) unset($child["value"]);

                        # Create the input line
                        $input = '<' . $tag . $this->implode_attributes($child) . '>' . $value . '</' . $tag . '>';

                        # These outputs require the special classing
                        $output .= '<div class="form-group ' . $error . '">
                                        ' . $label . '
                                        ' . $input . '
                                    </div>';
                    }

                    # Add the custom HTML as is
                    if ($tag == "custom") {

                        $output .= $value;
                    }

                    # We also need to be able to
                    if ($tag == "select") {

                        # REMOVE THE VALUE TAG FROM THE LIST
                        if ($value) unset($child["value"]);

                        $values = array();
                        if (!empty($child["value"])) unset($child["value"]);

                        foreach((array)$value as $val)
                        {
                            if (!empty($options[$val])) $values[] = $options[$val];
                        }

                        # Create the input line
                        $input = '<' . $tag . $this->implode_attributes($child) . '>' . array2options($options, $values, true) . '</' . $tag . '>';

                        # These outputs require the special classing
                        $output .= '<div class="form-group ' . $error . '">
                                        ' . $label . '
                                        ' . $input . '
                                    </div>';
                    }

                }
            }

            $output .= "</form>";
            return $output;
        }
    }