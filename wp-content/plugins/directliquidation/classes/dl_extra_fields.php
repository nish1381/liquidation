<?php

class DL_Extra_Fields {

    private static $instance = null;

    public static function getInstance()
    {
        if (null === self::$instance) {
            self::$instance = new self;
        }
        return self::$instance;
    }

    public function init()
    {
        add_action('admin_menu', array($this, 'addAdminMenu'));
        add_action('wp_ajax_dl_ef_editor', array($this, 'ajax'));

    }

    public function addAdminMenu() {
        add_menu_page('DL Extra Fields', 'Extra Fields', 'level_10', 'dl-extra-fields', array($this, 'show'), '', 59);
    }

    public function ajax() {
        try {
            $productOptions = get_option("dl_product_options", array());
            $maxId = get_option("dl_product_options_max_id", 0);
            $codes = array();
            foreach ($productOptions as $option) {
                $code = isset($option['code']) ? $option['code'] : "field_{$option['id']}";
                $codes[$code] = $option['id'];
            }
            if ($_POST['_action'] == 'edit') {
                $id = $_POST['id'];
                if ($id != 0 && !isset($productOptions[$id])) {
                    throw new Exception("Field not found");
                }
                $code = trim($_POST['code']);
                if ($code == '') {
                    throw new Exception("Empty code");
                }
                if (isset($codes[$code]) && $codes[$code] != $id) {
                    throw new Exception("Code is used by another field");
                }
                $name = trim($_POST['name']);
                if ($name == '') {
                    throw new Exception("Empty name");
                }
                $description = trim($_POST['description']);
                $order = intval($_POST['order']);
                $type = $_POST['type'];
                if (!in_array($type, array('string', 'int', 'double', 'datetime', 'bool', 'enum'))) {
                    throw new Exception("Unknown type");
                }
                $values = array();
                if ($type == 'enum') {
                    if (!isset($_POST['values']) || !is_array($_POST['values'])) {
                        throw new Exception("Enumerated list values empty");
                    }
                    foreach ($_POST['values'] as $value) {
                        $value = trim($value);
                        if ($value != '') {
                            $values[$value] = $value;
                        }
                    }
                    if (empty($values)) {
                        throw new Exception("Enumerable list values empty");
                    }
                }
                if ($id == 0) {
                    $maxId++;
                    $id = $maxId;
                    $option = array('id' => $id);
                } else {
                    $option = $productOptions[$id];
                }
                $option['code'] = $code;
                $option['name'] = $name;
                $option['description'] = $description;
                $option['type'] = $type;
                $option['order'] = $order;
                $option['values'] = $values;
                $productOptions[$id] = $option;
                uasort($productOptions, function($a, $b) {
                    return intval($a['order']) - intval($b['order']);
                });
                update_option('dl_product_options', $productOptions);
                update_option('dl_product_options_max_id', $maxId);
                echo json_encode(array(
                    'success' => true,
                    'options' => get_option("dl_product_options", array()),
                ));
                die();
            }
            if ($_POST['_action'] == 'delete') {
                $id = $_POST['id'];
                if ($id != 0 && !isset($productOptions[$id])) {
                    throw new Exception("Field not found");
                }
                unset($productOptions[$id]);
                update_option('dl_product_options', $productOptions);
                echo json_encode(array(
                    'success' => true,
                    'options' => get_option("dl_product_options", array()),
                ));
                die();
            }
        } catch (Exception $e) {
            echo json_encode(array('error' => $e->getMessage(), 'success' => false));
            die();
        }
    }

    public function show() {
        ?>
        <div class="wrap" id="dl_extra_fields">
            <style type="text/css" scoped="scoped">
                #dl_extra_fields_list .template,
                #dl_extra_fields_list .hidden {
                    display: none;
                }
            </style>
            <script type="text/javascript">
                var ajax_url = '<?php echo addslashes(admin_url('admin-ajax.php').'?action=dl_ef_editor') ?>';
                var fields = null;
                var maxCode = 0;
                var maxOrder = 0;
                jQuery(function($) {
                    var root = $('#dl_extra_fields');
                    var efl = root.find('[data-ef="list"]');
                    var template = efl.find('[data-ef="template"]').remove().removeClass('template');
                    var list = $('#dl_extra_fields_list');
                    var edit = $('#dl_extra_fields_edit');
                    var enumTemplate = edit.find('[data-ef="enum-template"]').remove();
                    var enumAdd = edit.find('[data-ef="add-enum"]').parent();
                    var types = {
                        'string': 'String',
                        'int': 'Integer',
                        'double': 'Real',
                        'datetime': 'Date and time',
                        'bool': 'Flag',
                        'enum': 'Enumerable'
                    };
                    var DL_Field = function(data) {
                        this.id = data.id;
                        this.name = data.name;
                        this.description = data.description;
                        this.order = data.order;
                        if (data.hasOwnProperty('code')) {
                            this.code = data.code;
                        } else {
                            this.code = 'field_' + data.id;
                        }
                        if (data.hasOwnProperty('type')) {
                            this.type = data.type;
                        } else {
                            this.type = 'string';
                        }
                        if (data.hasOwnProperty('values')) {
                            this.values = data.values;
                        } else {
                            this.values = [];
                        }
                    };
                    DL_Field.prototype.getType = function() {
                        return types[this.type];
                    };
                    var populateList = function(f) {
                        fields = f;
                        var sorted = [];
                        for (var i in fields) {
                            if (fields.hasOwnProperty(i)) {
                                sorted.push(new DL_Field(fields[i]));
                            }
                        }
                        sorted.sort(function(a, b) {
                            return Number(a.order) - Number(b.order);
                        });
                        efl.empty();
                        $.each(sorted, function() {
                           var field = this;
                            var row = template.clone();
                            row.data('id', field.id);
                            row.find('[data-ef]').each(function() {
                                var el = $(this);
                                switch (el.data('ef')) {
                                    case 'id':
                                    case 'code':
                                    case 'name':
                                    case 'order':
                                        el.text(field[el.data('ef')]);
                                        break;
                                    case 'type':
                                        el.text(field.getType());
                                        break;
                                }
                            });
                            if (Number(field.order) > maxOrder) {
                                maxOrder = Number(field.order);
                            }
                            var m = field.code.match(/^field_(\d+)$/);
                            if (m) {
                                if (Number(m[1]) > maxCode) {
                                    maxCode = Number(m[1]);
                                }
                            }
                            efl.append(row);
                        });
                    };
                    populateList(<?php echo json_encode(get_option("dl_product_options", array())); ?>);
                    edit.find('select[name=type]').on('change click keyup', function() {
                        if ($(this).val() == 'enum') {
                            edit.find('[data-ef="enum-holder"]').removeClass('hidden');
                        } else {
                            edit.find('[data-ef="enum-holder"]').addClass('hidden');
                        }
                    });
                    var doEdit = function(field) {
                        edit.find('input[name]').each(function() {
                            var el = $(this);
                            var name = el.attr('name');
                            switch (name) {
                                case 'id':
                                case 'order':
                                case 'name':
                                case 'code':
                                case 'description':
                                    el.val(field[name]);
                            }
                        });
                        edit.find('[data-ef-enum="row"]').remove();
                        $.each(field.values, function() {
                            var t = enumTemplate.clone();
                            t.find('input').val(this);
                            t.insertBefore(enumAdd);
                        });
                        if (field.values.length == 0) {
                            enumTemplate.clone().insertBefore(enumAdd);
                        }
                        edit.find('select[name=type]').val(field.type).trigger('change');
                        list.addClass('hidden');
                        edit.removeClass('hidden');
                    };
                    $(root).on('click', '[data-ef="add-new"]', function(e) {
                        e.preventDefault();
                        doEdit(new DL_Field({
                            id: 0,
                            order: String(maxOrder + 1),
                            code: 'field_' + String(maxCode + 1),
                            name: '',
                            description: '',
                            type: 'string'
                        }));
                    });
                    $(root).on('click', '[data-ef="edit"]', function(e) {
                        e.preventDefault();
                        var id = $(this).closest('[data-id]').data('id');
                        doEdit(new DL_Field(fields[id]));
                    });
                    $(root).on('click', '[data-ef="delete"]', function(e) {
                        e.preventDefault();
                        if (window.confirm('Are you sure want to delete this field')) {
                            var id = $(this).closest('[data-id]').data('id');
                            $.post(ajax_url, {'_action': 'delete', 'id': id}, function(ret) {
                                console.log(ret);
                                if (!ret.success) {
                                    alert(ret.error);
                                } else {
                                    alert('Field deleted');
                                    populateList(ret.options);
                                }
                            }, 'json');
                        }
                    });
                    $(root).on('click', '[data-ef="delete-enum"]', function(e) {
                        e.preventDefault();
                        var id = $(this).closest('[data-ef-enum="row"]').remove();
                    });
                    $(root).on('click', '[data-ef="add-enum"]', function(e) {
                        e.preventDefault();
                        enumTemplate.clone().insertBefore(enumAdd);
                    });
                    edit.submit(function(e) {
                        e.preventDefault();
                        var form = $(this);
                        var data = {
                            'id': form.find('[name=id]').val(),
                            'code': form.find('[name=code]').val(),
                            'name': form.find('[name=name]').val(),
                            'description': form.find('[name=description]').val(),
                            'type': form.find('[name=type]').val(),
                            'order': form.find('[name=order]').val(),
                            '_action': 'edit'
                        };
                        if (data.type == 'enum') {
                            data.values = [];
                            edit.find('[data-ef-enum="row"] input').each(function() {
                                data.values.push($(this).val());
                            });
                        }
                        $.post(ajax_url, data, function(ret) {
                            console.log(ret);
                            if (!ret.success) {
                                alert(ret.error);
                            } else {
                                alert('Information saved');
                                populateList(ret.options);
                                edit.addClass('hidden');
                                list.removeClass('hidden');
                            }
                        }, 'json');
                    });
                });
            </script>
            <div id="dl_extra_fields_list">
                <h2>Product extra fields <a class="add-new-h2" data-ef="add-new" href="#">Add field</a></h2>
                <table class="widefat" cellspacing="0">
                    <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Code</th>
                        <th scope="col">Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Order</th>
                        <th scope="col">Actions</th>
                    </tr>
                    </thead>
                    <tfoot>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Code</th>
                        <th scope="col">Name</th>
                        <th scope="col">Type</th>
                        <th scope="col">Order</th>
                        <th scope="col">Actions</th>
                    </tr>
                    </tfoot>
                    <tbody data-ef="list">
                    <tr class="template" data-ef="template" data-id="0">
                        <td data-ef="id"></td>
                        <td data-ef="code"></td>
                        <td data-ef="name"></td>
                        <td data-ef="type"></td>
                        <td data-ef="order"></td>
                        <td data-ef="actions">
                            <a href="#" data-ef="edit">Edit</a>
                            <a href="#" data-ef="delete">Delete</a>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
            <form id="dl_extra_fields_edit" class="hidden">
                <input type="hidden" name="id" value=""/>
                <h2>Edit field</h2>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">
                            <label for="dl_extra_fields_code">Code</label>
                        </th>
                        <td>
                            <input id="dl_extra_fields_code" class="regular-text" type="text" value="" name="code" required="required"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="dl_extra_fields_name">Name</label>
                        </th>
                        <td>
                            <input id="dl_extra_fields_name" class="regular-text" type="text" value="" name="name" required="required"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="dl_extra_fields_description">Description</label>
                        </th>
                        <td>
                            <input id="dl_extra_fields_description" class="regular-text" type="text" value="" name="description"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="dl_extra_fields_order">Order</label>
                        </th>
                        <td>
                            <input id="dl_extra_fields_order" class="regular-text" type="text" value="" name="order"/>
                        </td>
                    </tr>
                    <tr valign="top">
                        <th scope="row">
                            <label for="dl_extra_fields_type">Type</label>
                        </th>
                        <td>
                            <select id="dl_extra_fields_type" name="type">
                                <option value="string">String</option>
                                <option value="int">Integer</option>
                                <option value="double">Real</option>
                                <option value="datetime">Date and time</option>
                                <option value="bool">Flag</option>
                                <option value="enum">Enumerable</option>
                            </select>
                        </td>
                    </tr>
                    <tr valign="top" data-ef="enum-holder">
                        <th scope="row">
                            <label for="dl_extra_fields_values">Enum values</label>
                        </th>
                        <td>
                            <div data-ef="enum-template" data-ef-enum="row">
                                <input type="text" value /> <a href="#" data-ef="delete-enum">x</a>
                            </div>
                            <div><a href="#" data-ef="add-enum">Add New</a></div>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input id="submit" class="button button-primary" type="submit" value="Save Changes">
                </p>
            </form>
        </div>
    <?php
    }



}
 