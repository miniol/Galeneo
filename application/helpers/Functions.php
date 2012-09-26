<?php
/**
 * @copyright Roy Rosenzweig Center for History and New Media, 2007-2010
 * @license http://www.gnu.org/licenses/gpl-3.0.txt
 * @package Omeka_ThemeHelpers
 * @subpackage GeneralHelpers
 */

/**
 * Retrieve the view object.  Should be used only to avoid function scope
 * issues within other theme helper functions.
 *
 * @since 0.10
 * @access private
 * @return Omeka_View
 */
function get_view()
{
    return Zend_Registry::get('view');
}

/**
 * Output a <link> tag for the RSS feed so the browser can auto-discover the field.
 *
 * @since 1.4
 * @uses items_output_url()
 * @return string HTML
 */
function auto_discovery_link_tags() {
    $html = '<link rel="alternate" type="application/rss+xml" title="'. __('Omeka RSS Feed') . '" href="'. html_escape(items_output_url('rss2')) .'" />';
    $html .= '<link rel="alternate" type="application/atom+xml" title="'. __('Omeka Atom Feed') .'" href="'. html_escape(items_output_url('atom')) .'" />';
    return $html;
}

/**
 * Includes a file from the common/ directory, passing variables into that script.
 *
 * @param string $file Filename
 * @param array $vars A keyed array of variables to be extracted into the script
 * @param string $dir Defaults to 'common'
 * @return void
 */
function common($file, $vars = array(), $dir = 'common')
{
    return get_view()->partial($dir . '/' . $file . '.php', $vars);
}

/**
 * Include the header script into the view
 *
 * @see common()
 * @param array Keyed array of variables
 * @param string $file Filename of header script (defaults to 'header')
 * @return void
 */
function head($vars = array(), $file = 'header')
{
    return common($file, $vars);
}

/**
 * Include the footer script into the view
 *
 * @param array Keyed array of variables
 * @param string $file Filename of footer script (defaults to 'footer')
 * @return void
 */
function foot($vars = array(), $file = 'footer') {
    return common($file, $vars);
}

/**
 * Retrieve a flashed message from the controller
 *
 * @return string
 */
function flash()
{
    return get_view()->flash();
}

/**
 * Retrieve the value of a particular site setting.  This can be used to display
 * any option that would be retrieved with get_option().
 *
 * Content for any specific option can be filtered by using a filter named
 * 'display_option_(option)' where (option) is the name of the option, e.g.
 * 'display_option_site_title'.
 *
 * @uses get_option()
 * @since 0.9
 * @return string
 */
function option($name)
{
    $name = apply_filters("display_option_$name", get_option($name));
    $name = html_escape($name);
    return $name;
}

/**
 * Get a set of records from the database.
 *
 * @since 2.0
 * @uses Omeka_Db_Table::findBy
 *
 * @param string $recordType Type of records to get.
 * @param array $params Array of search parameters for records.
 * @param integer $limit Maximum number of records to return.
 * 
 * @return array An array of result records (of $recordType).
 */
function get_records($recordType, $params = array(), $limit = 10)
{
    return get_db()->getTable($recordType)->findBy($params, $limit);
}

/**
 * Get the total number of a given type of record in the database.
 *
 * @since 2.0
 * @uses Omeka_Db_Table::count
 *
 * @param string $recordType Type of record to count.
 *
 * @return integer Number of records of $recordType in the database.
 */
function total_records($recordType)
{
    return get_db()->getTable($recordType)->count();
}

/**
 * Return an iterator used for looping an array of records.
 * 
 * @uses Omeka_View_Helper_LoopRecords
 * @param string $recordsVar
 * @param array|null $records
 * @return Omeka_Record_Iterator
 */
function loop($recordsVar, $records = null)
{
    return get_view()->loopRecords($recordsVar, $records);
}

/**
 * Set records to the view for iteration.
 * 
 * @param string $recordsVar
 * @param array $records
 */
function set_loop_records($recordsVar, array $records)
{
    get_view()->setLoopRecords($recordsVar, $records);
}

/**
 * Get records from the view for iteration.
 * 
 * @param string $recordsVar
 * @return array|null
 */
function get_loop_records($recordsVar, $throwException = true)
{
    return get_view()->getLoopRecords($recordsVar, $throwException);
}

/**
 * Check if records have been set to the view for iteration.
 * 
 * @param string $recordsVar
 * @return bool
 */
function has_loop_records($recordsVar)
{
    return (bool) get_view()->getLoopRecords($recordsVar, false);
}

/**
 * Set a record to the view as the current record.
 * 
 * @uses Omeka_View_Helper_SetCurrentRecord
 * @param string $recordVar
 * @param Omeka_Record_AbstractRecord $record
 * @param bool $setPreviousRecord
 */
function set_current_record($recordVar, Omeka_Record_AbstractRecord $record, $setPreviousRecord = false)
{
    get_view()->setCurrentRecord($recordVar, $record, $setPreviousRecord);
}

/**
 * Get the current record from the view.
 * 
 * @uses Omeka_View_Helper_GetCurrentRecord
 * @throws Omeka_View_Exception
 * @param string $recordVar
 * @param bool $throwException
 * @return Omeka_Record_AbstractRecord|false
 */
function get_current_record($recordVar, $throwException = true)
{
    return get_view()->getCurrentRecord($recordVar, $throwException);
}

/**
 * Get a record by its ID.
 * 
 * @param string $recordVar
 * @param int $recordId
 * @return Omeka_Record_AbstractRecord|null
 */
function get_record_by_id($recordVar, $recordId)
{
    return get_db()->getTable(Inflector::camelize($recordVar))->find($recordId);
}

/**
 * Get all output formats available in the current action.
 *
 * @return array A sorted list of contexts.
 */
function get_current_action_contexts()
{
    $actionName = Zend_Controller_Front::getInstance()->getRequest()->getActionName();
    $contexts = Zend_Controller_Action_HelperBroker::getStaticHelper('contextSwitch')->getActionContexts($actionName);
    sort($contexts);
    return $contexts;
}

/**
 * Builds an HTML list containing all available output format contexts for the
 * current action.
 *
 * @param bool True = unordered list; False = use delimiter
 * @param string If the first argument is false, use this as a delimiter.
 * @return string HTML
 */
function output_format_list($list = true, $delimiter = ' | ')
{
    $actionContexts = get_current_action_contexts();
    $html = '';

    // Do not display the list if there are no output formats available in the
    // current action.
    if (empty($actionContexts)) {
        return false;
    }

    // Unordered list format.
    if ($list) {
        $html .= '<ul id="output-format-list">';
        foreach ($actionContexts as $key => $actionContext) {
            $query = $_GET;
            $query['output'] = $actionContext;
            $html .= '<li><a href="' . html_escape(url() . '?' . http_build_query($query)) . '">' . $actionContext . '</a></li>';
        }
        $html .= '</ul>';

    // Delimited format.
    } else {
        $html .= '<p id="output-format-list">';
        foreach ($actionContexts as $key => $actionContext) {
            $query = $_GET;
            $query['output'] = $actionContext;
            $html .= '<a href="' . html_escape(url() . '?' . http_build_query($query)) . '">' . $actionContext . '</a>';
            $html .= (count($actionContexts) - 1) == $key ? '' : $delimiter;
        }
        $html .= '</p>';
    }

    return $html;
}

function browse_headings($headings)
{
    $sortParam = Omeka_Db_Table::SORT_PARAM;
    $sortDirParam = Omeka_Db_Table::SORT_DIR_PARAM;
    $req = Zend_Controller_Front::getInstance()->getRequest();
    $currentSort = trim($req->getParam($sortParam));
    $currentDir = trim($req->getParam($sortDirParam));

    foreach ($headings as $label => $column) {
        if($column) {
            $urlParams = $_GET;
            $urlParams[$sortParam] = $column;
            $class = '';
            if ($currentSort && $currentSort == $column) {
                if ($currentDir && $currentDir == 'd') {
                    $class = 'class="sorting desc"';
                    $urlParams[$sortDirParam] = 'a';
                } else {
                    $class = 'class="sorting asc"';
                    $urlParams[$sortDirParam] = 'd';
                }
            }
            $url = url(array(), null, $urlParams);
            echo "<th $class scope=\"col\"><a href=\"$url\">$label</a></th>";
        } else {
            echo "<th scope=\"col\">$label</th>";
        }
    }
}

/**
 * Returns a <body> tag with attributes. Attributes
 * can be filtered using the 'body_tag_attributes' filter.
 *
 * @since 1.4
 * @uses tag_attributes()
 * @return string An HTML <body> tag with attributes and their values.
 */
function body_tag($attributes = array())
{
    $attributes = apply_filters('body_tag_attributes', $attributes);
    if ($attributes = tag_attributes($attributes)) {
        return "<body ". $attributes . ">\n";
    }
    return "<body>\n";
}

/**
 * Return a list of the current search filters in use.
 *
 * @since 2.0
 * @params array $params Optional params to replace the ones read from the request.
 */
function search_filters(array $params = null)
{
    return get_view()->searchFilters($params);
}

/**
 * Get a piece or pieces of metadata for a record.
 *
 * @see Omeka_View_Helper_RecordMetadata
 * @param Omeka_Record_AbstractRecord|string $record The record to get metadata
 *  for. If an Omeka_Record_AbstractRecord, that record is used. If a string,
 *  that string is used to look up a record in the current view.
 * @param mixed $metadata The metadata to get. If an array is given, this is
 *  Element metadata, identified by array('Element Set', 'Element'). If a string,
 *  the metadata is a record-specific "property."
 * @param array $options Options for getting the metadata.
 * @return mixed
 */
function metadata($record, $metadata, $options = array())
{
    return get_view()->recordMetadata($record, $metadata, $options);
}

/**
 * Retrieve the set of all element text metadata for a record.
 *
 * @since 2.0
 * @uses Omeka_View_Helper_RecordMetadataList
 * 
 * @param Omeka_Record_AbstractRecord|string $record The record to get the
 *  element text metadata for.
 * @param array $options Options for getting the metadata.
 * @return string|array
 */
function all_element_texts($record, $options = array())
{
    return get_view()->recordMetadataList($record, $options);
}
