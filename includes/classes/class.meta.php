<?php

    # Author: Rudi Strydom
    # Date: Aug 2013
    # Purpose: In an attempt to make things as generic as possible I will
    # be creating more classes which can be used for multiple purposes. For iunstance meta_data
    class Meta
    {
        # We need some variables to get started.
        private $db = null;
        public $meta_data = array();
        public $type = null;

        # Let's get going shall we?
        function __construct($type)
        {
            # Connect to the db AND select the relevant information from the db based on the active meta and the type of meta it is
            $this->db   = Database::getDatabase();
            $sql        = "SELECT
                            `id`, `active`, `type`, `name`, `date`
                        FROM
                            `meta`
                        ORDER By `name` ASC";
            $select     = $this->db->query($sql);
            $results    = $this->db->getRows();

            # Should there be valid meta_data
            if (!empty($results))
            {
                # Set them for this type of instance
                $this->meta_data = $results;
            }

            # Remember to set the type so we can re-use it
            $this->type = $type;
            return $this->meta_data;
        }


        # Get the list of available meta to list through
        public function available()
        {
            # Ensure that we actually have meta data to work with
            if (!empty($this->meta_data))
            {
                # We need a holder
                $types = array();

                # Loop through the me
                foreach($this->meta_data as $meta)
                {
                    # Ensure that the type is not in the array yet
                    if (!in_array($meta["type"], $types))
                    {
                        # Add it
                        $types[] = $meta["type"];
                    }
                }
                return $types;
            }
            return false;
        }

        # Simple as can be, we need to set the type
        function setType($type)
        {
            $this->type = $type;
            return true;
        }

        # Update or insert
        function upsert($info)
        {
            # Existing Record
            if (isset($info['id']))
            {
                # Let's attempt an update query
               $upsert = $this->db->query("UPDATE
                                                `meta`
                                            SET
                                                `active`=:active:,
                                                `type`=" . $this->db->quote($this->type) . ",
                                                `name`=:name:,
                                                `date`='" . time() . "'
                                            WHERE id=:id:",
                                            array(
                                                "active"    => $info['active'],
                                                "name"      => $info['name'],
                                                "id"        => $info['id']
                                                )
                                            );

            # New Record
            } else {

                # Let's attempt to do an insert
                $upsert = $this->db->query("INSERT INTO
                                            `meta`
                                                (`active`, `type`, `name`, `date`)
                                            VALUES
                                                (1, " . $this->db->quote($this->type) . ", :name:, " . time() . ")",
                                            array(
                                                "name"      => $info['name']
                                                )
                                            );
            }

            # Check that the action was successfull, and return either false, or the id of the object
            if ($upsert)
            {
                if (isset($info['id'])) {
                    return $info['id'];
                } else {
                    return $this->db->insertId();
                }
            }

            # Otherwise default to a false
            return false;
        }


        # We also need to be able to get a simple version back
        function getSimple($id=false)
        {
            $output = array();

            # Ensure that the array is not empty
            if (!empty($this->meta_data))
            {
                # If there is an id
                if ($id)
                {
                    # Then loop through the meta_data
                    foreach($this->meta_data as $meta)
                    {
                        # Should we find a matching entry, then only return the name
                        if ($meta['id'] == $id)
                        {
                            return $meta['name'];
                        }
                    }

                # Otherwise
                } else {

                    # loop through all the meta data
                    foreach($this->meta_data as $meta)
                    {
                        //printr($meta);
                        # Ensure that it's the type we are currently dealing with
                        if ($this->type == $meta['type'])
                        {
                            # And return all the information in an id=>name pair
                            $output[$meta['id']] = $meta['name'];
                        }
                    }
                }
            }

            # Default to false
            return $output;
        }


        # Use this function to get a full list of the active meta
        function getActive($id=false)
        {
            # Get the list
            $get = $this->get($id);
            $output = array();

            # Loop through the info
            foreach($get as $gid=>$g)
            {
                if ($g['active'] == 1)
                {
                    $output[$gid] = $g;
                }
            }
            return $output;
        }


        # Use this function to get a full list of the active meta
        function getSimpleActive($id=false)
        {
            # Get the list
            $get = $this->get($id);
            $output = array();

            # Loop through the info
            foreach((array)$get as $gid=>$g)
            {
                if ($g['active'] == 1)
                {
                    $output[$gid] = $g['name'];
                }
            }
            return $output;
        }


        # Get the array of info from the db
        function get($id=false)
        {
            # Ensure that the array is not empty
            if (!empty($this->meta_data))
            {
                # If there is an id
                if ($id)
                {
                    # Then loop through the meta_data
                    foreach((array)$this->meta_data as $meta)
                    {
                        if ($meta['id'] == $id)
                        {
                            return $meta;
                        }
                    }

                # Otherwise
                } else {

                    $output = array();

                    # loop through all the meta data
                    foreach($this->meta_data as $meta)
                    {
                        # Ensure that it's the type we are currently dealing with
                        if ($this->type == $meta['type'])
                        {
                            # And return all the information in an id=>name pair
                            $output[$meta['id']] = $meta;
                        }
                    }

                    # Return the output
                    return $output;
                }
            }

            # Default to false
            return false;
        }

        # Check if the entry exists based on an aspect
        function exists($search_string, $field="name")
        {
            # Ensure that the array is not empty
            if (!empty($this->meta_data))
            {
                # Then loop through the meta_data
                foreach((array)$this->meta_data as $meta)
                {
                    if (($meta[$field] == $search_string) AND ($this->type == $meta['type']))
                    {
                        return $meta;
                    }
                }
            }

            # Default to false
            return false;
        }


        # Do a whildcard search
        function search($search_string, $field="name")
        {
            $found = array();

            # Ensure that the array is not empty
            if (!empty($this->meta_data))
            {
                # Then loop through the meta_data
                foreach((array)$this->meta_data as $meta)
                {
                    if ((stristr($meta[$field], $search_string)) AND ($this->type == $meta['type']) AND ($meta['active'] == 1))
                    {
                        $found[$meta['id']] = $meta;
                    }
                }

                # If there were entries found then return them
                if (!empty($found)) return $found;
            }

            # Default to false
            return false;
        }


        # The form expects you to provide and array of the meta entry to be able to
        function manage($return_to_page="index.php", $id=false)
        {
            # Let's get things started
            $name           = "";
            $output         = "";
            $desc           = "Add more " . $this->type . " information";
            $formzy         = new Form(array("class"=>"form-vertical"));
            $action         = "Save New";
            $search         = (!empty($_REQUEST['search'])) ? $_REQUEST['search'] : false;
            $meta_data      = $this->meta_data;

            # Should there be a search
            if ($search)
            {
                # We may want to be able to display only relevant records
               $meta_data = $this->search($_REQUEST["search"]);
            }

            # Add the mini form for the search query
            $output .= "<form method='post' class='form'>
                            <div class='row'>
                                <div class='col-sm-4'>
                                    <div class='input-group'>
                                        <input class='form-control' type='text' name='search' placeholder='Looking for something' value='" . @$search . "' />
                                        <span class='input-group-btn'>
                                            <button class='btn btn-primary' type='button'>Search</button>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </form>";

            # Check that the meta_data are not empty
            if (!empty($meta_data) AND !$id)
            {

                $output .= '<br /><br />
                            <p>
                                <b>Current ' . ucfirst($this->type) . ' List</b>
                            </p>
                            <div class="row">
                                <table class="table table-rounded table-hover table-bordered table-striped span6">
                                    <thead>
                                        <th>Name</th>
                                        <th class="col-sm-2">Actions</th>
                                    </thead>
                                <tbody>';

                # Then loop through them
                foreach((array)$meta_data as $meta)
                {
                    if ($this->type !== $meta['type']) continue;
                    if ($meta['active'] == 0) continue;

                    $links   = "<a class='btn btn-default btn-sm' href='?action=meta&id=" . $meta['id'] . "&type=" . rawurlencode( $this->type ) . "'>" . icon('pencil') . "</a>";
                    $links  .= "&nbsp;&nbsp;";
                    $links  .= "<a class='btn btn-danger btn-sm' href='?action=meta&id=" . $meta['id'] . "&type=" . rawurlencode( $this->type ) . "&sa=delete'>" . icon('trash') . "</a>";

                    $output .= "<tr>
                                    <td>" . $meta['name'] . "</td>
                                    <td>" . $links . "</td>
                                </tr>";
                }

                $output .= '        </tbody>
                                </table>
                            </div>
                            <hr />';
            }

            # Check if it's a returning
            if ($id)
            {
                # Attempt to get the details for the meta
                $getDetails = $this->get($id);

                # If it's valid then get the name from the db
                if ($getDetails) {
                    $name = $getDetails["name"];
                }

                $formzy->addHidden( array("name"=>"id", "value"=>$id) );
                $desc = "Edit " . ucfirst( $this->type ) . "'s information";
                $action = "Save Changes";

                # Check for any subactions
                if (isset($_REQUEST['sa']))
                {
                    # Check is a delete was requested
                    if ($_REQUEST['sa'] == "delete")
                    {
                        $getDetails['active'] = 0;
                        $upsert = $this->upsert($getDetails);
                        if ($upsert) redirect($return_to_page . "&error=info&message=Successfully removed entry");
                    }
                }
            }

            $mappingList = $this->available();
            $mappingList[0] = "";

            # Let's start adding some fields
            $formzy->addCustom("<b><p>" . $desc . "</p></b>");
            $formzy->addHidden( array("name"=>"active", "value"=>1) );
            $formzy->addTextarea( array("name"=>"name", "rows"=> 5, "value"=>$name, "label"=>"Name", "validation"=>array("long"=>2)) );
            $formzy->addSubmit( array("value"=>$action) );
            $output .= $formzy->render();

            # Check if the form has been submittted or not
            if ( $formzy->submitted() AND $formzy->valid() )
            {
                $failure = false;
                $entries = explode("\n", $_REQUEST['name']);

                foreach((array)$entries as $entry)
                {
                    $entry = trim($entry);
                    if (!empty($entry))
                    {
                        # Attempt the update / insert
                        if ($id)
                        {
                            # Ensure that we also provide the id
                            $upsert = $this->upsert( array("name"=>$entry, "id"=>$id, "active"=>1) );
                        }

                        # Otherwise it's a good old insert
                        else
                        {
                            $upsert = $this->upsert( array("name"=>$entry) );
                        }

                        if (!$upsert) $failure = true;
                    }
                }
                if (!$failure) redirect($return_to_page . "&error=info&message=Successfully updated list");
            }
            return $output;
        }
    }