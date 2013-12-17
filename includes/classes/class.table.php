<?php

    # Author: Rudi Strydom
    # Date: Aug 2013
    # Purpose: Building a quite and dirty table class. To transform an array to a table

    class Table
    {
        public $table_attributes        = array();
        public $table_heading           = array();
        public $table_children          = array();
        public $Error;

        # How does it all begin?
        function __construct( $overide_defaults=false )
        {

            # We need some basic form attributes to start off
            $this->table_attributes = array(
                "cellpadding"   => "0",
                "cellspacing"   => "0",
                "class"         => "table table-striped table-hover table-rounded table-bordered"
            );

            # We may also want to override the defaults on construct
            if ($overide_defaults)
            {

                # The override them
                $this->table_attributes = (array)$overide_defaults;
            }
        }


        # We may want to be able to alter or add some attributes
        function attributes($array_input=false)
        {

            # Ensure it is an array
            if (is_array($array_input))
            {
                # Cool it's an array.
                foreach($array_input as $attribute => $value)
                {
                    $this->table_attributes[$attribute] = $value;
                }
                return true;
            }
            return false;
        }

        # We may want to have some headers
        function heading($array)
        {
            $this->table_heading = $array;
            return true;
        }

        # Alias to heading
        function headers($array)
        {
            return $this->heading($array);
        }

        # Alias to heading
        function headings($array)
        {
            return $this->heading($array);
        }

        # Add more elements (Children)
        function addRow($array)
        {
            $this->table_children[] = $array;
            return true;
        }

        # We may also want to add a class
        function addClass($classes_to_add=false)
        {

            # Ensure it is an array
            if ($classes_to_add)
            {
                # Explode by space
                $classes = explode(' ', $this->table_attributes['class']);

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
                $this->table_attributes['class'] = implode(" ", $classes);
                return true;
            }
            return false;
        }


        # We may also want to remove a class
        function removeClass($classes_to_remove=false)
        {

            # Ensure it is an array
            if ($classes_to_remove)
            {
                # Explode by space
                $classes = explode(' ', $this->table_attributes['class']);

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
                $this->table_attributes['class'] = implode(" ", $classes);
                return true;
            }
            return false;
        }


        # This function will be used to implode an array into an HTML format
        function implode_attributes($array_input=false)
        {

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
                    if ($attribute == "value") continue;

                    # If it's empty
                    if ($value == "")
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

        # This function will end off the form and return it for output
        function render()
        {
            # Build up the form
            $output = "\n<table " . $this->implode_attributes( $this->table_attributes ) . ">\n";

            # Check if the heading are empty or not
            if (!empty($this->table_heading))
            {
                $output .= "\t<thead>\n";
                foreach($this->table_heading as $th)
                {
                    $value = $th;
                    $implode = "";
                    if (is_array($th))
                    {
                        $implode = $this->implode_attributes($th);
                        $value = $th['value'];
                    }
                    $output .= "\t\t<th " . $implode . ">" . $value . "</th>\n";
                }
                $output .= "\t</thead>\n";
            }

            # If there are children then loop through them
            if (!empty($this->table_children))
            {
                $output .= "\t<tbody>\n";
                foreach($this->table_children as $row)
                {
                    $output .= "\t<tr>\n";
                    foreach($row as $td)
                    {
                        $value = $td;
                        $implode = "";
                        if (is_array($td))
                        {
                            $implode = $this->implode_attributes($td);
                            $value = $td['value'];
                        }
                        $output .= "\t\t<td " . $implode . ">" . $value . "</td>\n";
                    }
                    $output .= "\t</tr>\n";
                }
                $output .= "\t</tbody>\n";
            }

            $output .= "</table>\n";
            return $output;
        }
    }