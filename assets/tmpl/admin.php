<?php
//Check if this is called from the application
if(!defined('SPF'))
{
    header('Location:/');
    exit();
}
?>
    <form method='post' action=''>
	<ul class="nav nav-pills" id="myTab">

<?php
    $i = 0;
    foreach(Options::groups() as $group)
    {
        $active = '';
        if ($i == 0) $active = "active";
        $hash = strtolower(str_replace(" ", "", $group['group']));
        $title = $group['desc'];
        echo '<li class="' . $active . '">
                <a data-toggle="tab" title="' . $title . '" href="#' . $hash . '">' . $group["group"] . '</a>
            </li>';

        $i++;
    }
?>
    </ul>
    <div id="myTabContent" class="tab-content">

<?php
    $i = 0;
    foreach(Options::groups() as $group)
    {
        $active = '';
        if ($i == 0) $active = " active in";
        $hash = strtolower(str_replace(" ", "", $group['group']));
        $title = $group['desc'];
        $name = $group['group'];

        echo '<div id="' . $hash . '" class="tab-pane fade' . $active . '">
                    <h3>' . $name . ' <small>' . $title . '</small></h3>
                    <br />';

        $options = (Options::getList(false, $name));

        if (empty($options))
        {
            echo "No results found";
        }

        else
        {
            foreach($options as $option=>$value)
            {
                $type = $value['type'];
                $val = $value['value'];
                $label = camelcase2space($option);

                echo '<div class="form-group">
                        <label class="control-label" for="' . $option . '">' . $label . '</label>
                        <div class="controls">';

                if ($type == 'input')
                {
                    echo '<input type="text" class="form-control input-sm" id="' . $option . '" name="' . $option . '" value="' . $val . '">';
                }

                if ($type == 'textarea')
                {
                    echo '<textarea class="form-control  input-sm" id="' . $option . '" name="' . $option . '">' . $val . '</textarea>';
                }

                if ($type == 'bool')
                {
		           echo bool_select($val, $option);
                }

                if ($type == 'date')
                {
                    echo mdy($val, $option, 'd m y');
                }

                echo "</div></div>";
            }
        }
        echo '</div>
            <div class="clearfix"></div>';


     //. Options::all($name) . "</div>";

        $i++;
    }
?>
    </div>
        <div class="form-group">
            <button type="submit" name="action" value='Save Changes' class="btn btn-default btn-lg btn-primary">Save Changes</button>
        </div>
    </form>

    <hr />

    <form method="post" class="form">
        <h3>Add an option (PHP Variable)</h3>
        <br />

        <div class="form-group">
            <input type="text" class="form-control" name="option_name" placeholder="Use camel case like userName">
        </div>

        <div class="form-group">
            <input type="text" class="form-control" name="option_value" placeholder="Default Value">
        </div>

        <div class="form-group">
            <select class="form-control" name='group' title='Groups of options'>
                <?php echo get_options('options_groups', 'group', 'group', @$_POST['group']); ?>
            </select>
        </div>

        <div class="form-group">
            <select class="form-control" name='type' title='Type of variable'>
                <?php echo array2options(Options::types(), @$_POST['type']); ?>
            </select>
        </div>
        <input type='submit' class='btn btn-default' value='Add Option'>
        <input type='hidden' name='action' value='add'>
    </form>

    <hr />

    <form method="post" class="form">
        <h3>Add Group</h3>
        <br />

        <div class="form-group">
            <input type="text" class="form-control" name="group_name" placeholder="MyGroup Name">
        </div>

        <div class="form-group">
            <input type="text" class="form-control" name="group_desc" placeholder="Description of the group">
        </div>
        <input type='submit' class='btn btn-default' value='Add Group'>
        <input type='hidden' name='action' value='group_add'>
    </form>

    <hr />

    <form method="post" class="form">
        <h3>Link option to group</h3>
        <br />
        <div class="form-group">
            <select class="form-control" placeholder="Variable" id='option' name='option' title='Options Variables'>
                <?php echo get_options('options', 'key', 'key', @$_POST['option']); ?>
            </select>
        </div>

        <div class="form-group">
            <select class="form-control" name='group' id='group' title='Groups of options'>
                <?php echo get_options('options_groups', 'group', 'group', @$_POST['group']); ?>
            </select>
        </div>

        <div class="form-group">
            <select class="form-control" name='type' id='type' title='Type of variable'>
                <?php echo array2options(Options::types(), @$_POST['type']); ?>
            </select>
        </div>
        <input type='submit' class='btn btn-default' value='Update'>
        <input type='hidden' name='action' value='update'>
    </form>

    <hr />

    <form method="post">
        <h3>Remove Option</h3>
        <br />

        <div class="form-group">
            <select class="form-control" placeholder="Variable" id='option' name='option' title='Options Variables'>
                <?php echo get_options('options', 'key', 'key', @$_POST['option']); ?>
            </select>
        </div>
        <input type='submit' class='btn btn-default' value='Remove'>
        <input type='hidden' name='action' value='remove'>
    </form>

    <hr />

    <form method="post">
        <h3>Remove Group</h3>
        <br />

        <div class="form-group">
            <select class="form-control" name='group' id='group' title='Groups of options'>
                <?php echo get_options('options_groups', 'group', 'group', @$_POST['group']); ?>
            </select>
        </div>
        <input type='submit' class='btn btn-default' value='Remove Group'>
        <input type='hidden' name='action' value='group_remove'>
    </form>