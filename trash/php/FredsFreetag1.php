<?php
/***
 *  FredsFreetag1
 *  in which I make an initial stab at removing the *tagger* from the tag code
 * potential interventions and relevant documentation flagged first with [FC].
 * This contains a lot of tagger_id code, and has been superceded by FredsFreetag2.php
 ***/

/**
 *  Freetag - Simple PHP/MySQL tagging library
 *
 *  Released under both BSD license and Lesser GPL library license.  Whenever
 *  there is any discrepancy between the two licenses, the BSD license will
 *  take precedence. See License.txt.  
 *
 */
/**
 *  Freetag - Simple PHP/MySQL tagging library
 *
 *  Freetag is a generic PHP class that can hook in to existing database
 *  schemas and allows tagging of content within a social website. It's fun,
 *  fast, and easy!  Try it today and see what all the folksonomy fuss is
 *  about.
 * 
 *  Contributions welcome.
 * 
 *  http://github.com/freetag
 * 
 */ 
class freetag {
  /**#@+
   *  @access private
   *  @var string DB Connection parameters
   */ 
  var $_db_user = 'root';
  var $_db_pass = 'mycroft03';
  var $_db_host = 'localhost';
  var $_db_name = 'JS1'; 
  /**#@-*/
  /**
   * @access private
   * @var ADOConnection The ADODB Database connection instance.
   */
  var $_db;
  /**
   * @access private
   * @var string The db driver string to pass to ADOdb.
   */
  var $_db_driver = 'mysql';
  /**
   * @access private
   * @var bool Prints out limited debugging information if true, not fully implemented yet.
   */
  var $_debug = FALSE;
  /**
   * @access private
   * @var string The prefix of freetag database tables.
   */
  var $_table_prefix = '';
  /**
   * @access private
   * @var string The regex-style set of characters that are valid for normalized tags.
   */
  var $_normalized_valid_chars = 'a-zA-Z0-9';
  /**
   * @access private
   * @var string Whether to normalize tags at all.
   */
  var $_normalize_tags = 1;
  /**
   * @access private  [FC]
   * @var string Whether to prevent multiple users from tagging the same object. By default, set to block (ala Upcoming.org)
   */
  var $_block_multiuser_tag_on_object = 1;
  /**
   * @access private
   * @var string Will append this string to any integer tags sent through Freetag. This is supposed to prevent PHP casting "string" integer tags as ints. Won't do anything to floats or non-numeric strings.
   */
  var $_append_to_integer = '';
  /**
   * @access private
   * @var bool Whether to use persistent ADODB connections. False by default.
   */
  var $_PCONNECT = FALSE;
  /**
   * @access private
   * @var int The maximum length of a tag.
   */ 
  var $_MAX_TAG_LENGTH = 30;
  /**
   * @access private
   * @var string The file path to the installation of ADOdb used.
   */ 
  var $_ADODB_DIR = 'adodb/';
  /**
   * freetag
   *
   * Constructor for the freetag class. 
   *
   * @param array An associative array of options to pass to the instance of Freetag.
   * The following options are valid:
   * - debug: Set to TRUE for debugging information. [default:FALSE]
   * - db: If you've already got an ADODB ADOConnection, you can pass it directly and Freetag will use that. [default:NULL]
   * - db_user: Database username
   * - db_pass: Database password
   * - db_host: Database hostname [default: localhost]
   * - db_name: Database name
   * - table_prefix: If you wish to create multiple Freetag databases on the same database, you can put a prefix in front of the table names and pass separate prefixes to the constructor. [default: '']
   * - normalize_tags: Whether to normalize (lowercase and filter for valid characters) on tags at all. [default: 1]
   * - normalized_valid_chars: Pass a regex-style set of valid characters that you want your tags normalized against. [default: 'a-zA-Z0-9' for alphanumeric]
   * - block_multiuser_tag_on_object: Set to 0 in order to allow individual users to all tag the same object with the same tag. Default is 1 to only allow one occurence of a tag per object. [default: 1]
   * - append_to_integer: Will append this string to any integer tags sent through Freetag. This is supposed to prevent PHP casting "string" integer tags as ints. Won't do anything to floats or non-numeric strings.
   * - MAX_TAG_LENGTH: maximum length of normalized tags in chars. [default: 30]
   * - ADODB_DIR: directory in which adodb is installed. Change if you don't want to use the bundled version. [default: adodb/]
   * - PCONNECT: Whether to use ADODB persistent connections. [default: FALSE]
   * 
   */
  function freetag($options = NULL) {
    $available_options = array('debug', 'db', 'db_driver', 'db_user', 'db_pass', 'db_host', 'db_name', 'table_prefix', 'normalize_tags', 'normalized_valid_chars', 'block_multiuser_tag_on_object', 'append_to_integer', 'MAX_TAG_LENGTH', 'ADODB_DIR', 'PCONNECT');
    if (is_array($options)) {
      foreach ($options as $key => $value) {
        $this->debug_text("Option: $key");
        if (in_array($key, $available_options) ) {
          $this->debug_text("Valid Config options: $key");
          $property = '_'.$key;
          $this->$property = $value;
          $this->debug_text("Setting $property to $value");
        } else {
          $this->debug_text("ERROR: Config option: $key is not a valid option");
        }
      }
    }
    require_once($this->_ADODB_DIR . "/adodb.inc.php");
    if (is_object($this->_db)) {
      $this->db = &$this->_db;
      $this->debug_text("DB Instance already exists, using this one.");
    } else {
      $this->db = ADONewConnection($this->_db_driver);
      $this->debug_text("Connecting to db with:" . $this->_db_host . " " . $this->_db_user . " " . $this->_db_pass . " " . $this->_db_name);
      if ($this->_PCONNECT) {
        $this->db->PConnect($this->_db_host, $this->_db_user, $this->_db_pass, $this->_db_name);
      } else {
        $this->db->Connect($this->_db_host, $this->_db_user, $this->_db_pass, $this->_db_name);
      }
    }
    $this->db->debug = $this->_debug;
    // Freetag uses ASSOC for ease of maintenance and compatibility with people who choose to modify the schema.
    // Feel free to convert to NUM if performance is the highest concern.
    $this->db->SetFetchMode(ADODB_FETCH_ASSOC);
  }
  /**
   * get_objects_with_tag
   *
   * Use this function to build a page of results that have been tagged with the same tag.
   * Pass along a tagger_id [FC] to collect only a certain user's tagged objects, and pass along
   * none in order to get back all user-tagged objects. Most of the get_*_tag* functions
   * operate on the normalized form of tags, because most interfaces for navigating tags
   * should use normal form.
   *
   * @param string - Pass the normalized tag form along to the function.
   * @param int (Optional) - The numerical offset to begin display at. Defaults to 0.
   * @param int (Optional) - The number of results per page to show. Defaults to 100.
   * @param int (Optional) - The unique ID of the 'user' who tagged the object.  [FC]
   *
   * @return An array of Object ID numbers that reference your original objects.
   */ 
  function get_objects_with_tag($tag, $offset = 0, $limit = 100, $tagger_id = NULL) {
    if (!isset($tag)) {
      return false;
    }		
    $db = $this->db;
    $tag = $db->qstr($tag, get_magic_quotes_gpc());
    // [FC]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    $prefix = $this->_table_prefix;
    $sql = "SELECT DISTINCT object_id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE tag = $tag
			$tagger_sql
			ORDER BY object_id ASC
			LIMIT $offset, $limit
			";
    $rs = $db->Execute($sql) or die("Error: $sql");
    $retarr = array();
    while(!$rs->EOF) {
      $retarr[] = $rs->fields['object_id'];
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * get_objects_with_tag_all
   *
   * Use this function to build a page of results that have been tagged with the same tag.
   * This function acts the same as get_objects_with_tag, except that it returns an unlimited
   * number of results. Therefore, it's more useful for internal displays, not for API's.
   * Pass along a tagger_id to collect only a certain user's tagged objects [FC], and pass along
   * none in order to get back all user-tagged objects. Most of the get_*_tag* functions
   * operate on the normalized form of tags, because most interfaces for navigating tags
   * should use normal form.
   *
   * @param string - Pass the normalized tag form along to the function.
   * @param int (Optional) - The unique ID of the 'user' who tagged the object.
   *
   * @return An array of Object ID numbers that reference your original objects.
   */ 
  function get_objects_with_tag_all($tag, $tagger_id = NULL) {
    if (!isset($tag)) {
      return false;
    }		
    $db = $this->db;
    $tag = $db->qstr($tag, get_magic_quotes_gpc());
    // [FC]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    $prefix = $this->_table_prefix;
    $sql = "SELECT DISTINCT object_id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE tag = $tag
			$tagger_sql
			ORDER BY object_id ASC
			";
    $rs = $db->Execute($sql) or die("Error: $sql");
    $retarr = array();
    while(!$rs->EOF) {
      $retarr[] = $rs->fields['object_id'];
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * get_objects_with_tag_combo
   *
   * Returns an array of object ID's that have all the tags passed in the
   * tagArray parameter. Use this to provide tag combo services to your users.
   *
   * @param array - Pass an array of normalized form tags along to the function.
   * @param int (Optional) - The numerical offset to begin display at. Defaults to 0.
   * @param int (Optional) - The number of results per page to show. Defaults to 100.
   * @param int (Optional) - Restrict the result to objects tagged by a particular user. [FC]
   *
   * @return An array of Object ID numbers that reference your original objects.
   */
  function get_objects_with_tag_combo($tagArray, $offset = 0, $limit = 100, $tagger_id = NULL) {
    if (!isset($tagArray) || !is_array($tagArray)) {
      return false;
    }
    $db = &$this->db;
    $retarr = array();
    if (count($tagArray) == 0) {
      return $retarr;
    }
    // [FC]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    foreach ($tagArray as $key => $value) {
      $tagArray[$key] = $db->qstr($value, get_magic_quotes_gpc());
    }
    $tagArray = array_unique($tagArray);
    $tag_sql = join(",", $tagArray);
    $numTags = count($tagArray);
    $prefix = $this->_table_prefix;
    // We must adjust for duplicate normalized tags appearing multiple times in the join by 
    // counting only the distinct tags. It should also work for an individual user.
    $sql = "SELECT ${prefix}freetagged_objects.object_id, tag, COUNT(DISTINCT tag) AS uniques
			FROM ${prefix}freetagged_objects 
			INNER JOIN ${prefix}freetags ON (${prefix}freetagged_objects.tag_id = ${prefix}freetags.id)
			WHERE ${prefix}freetags.tag IN ($tag_sql)
			$tagger_sql
			GROUP BY ${prefix}freetagged_objects.object_id
			HAVING uniques = $numTags
			LIMIT $offset, $limit
			";
    $this->debug_text("Tag combo: " . join("+", $tagArray) . " SQL: $sql");
    $rs = $db->Execute($sql) or die("Error: $sql");
    while(!$rs->EOF) {
      $retarr[] = $rs->fields['object_id'];
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * get_objects_with_tag_id
   *
   * Use this function to build a page of results that have been tagged with the same tag.
   * This function acts the same as get_objects_with_tag, except that it accepts a numerical
   * tag_id instead of a text tag.
   * Pass along a tagger_id to collect only a certain user's tagged objects, and pass along
   * none in order to get back all user-tagged objects.
   *
   * @param int - Pass the ID number of the tag.
   * @param int (Optional) - The numerical offset to begin display at. Defaults to 0.
   * @param int (Optional) - The number of results per page to show. Defaults to 100.
   * @param int (Optional) - The unique ID of the 'user' who tagged the object. [FC]
   *
   * @return An array of Object ID numbers that reference your original objects.
   */ 
  function get_objects_with_tag_id($tag_id, $offset = 0, $limit = 100, $tagger_id = NULL) {
    if (!isset($tag_id)) {
      return false;
    }		
    $db = $this->db;
    // [FC]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    $prefix = $this->_table_prefix;
    $sql = "SELECT DISTINCT object_id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE id = $tag_id
			$tagger_sql
			ORDER BY object_id ASC
			LIMIT $offset, $limit
			";
    $rs = $db->Execute($sql) or die("Error: $sql");
    $retarr = array();
    while(!$rs->EOF) {
      $retarr[] = $rs->fields['object_id'];
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * get_tags_on_object
   *
   * You can use this function to show the tags on an object. Since it supports both user-specific
   * and general modes with the $tagger_id parameter, you can use it twice on a page to make it work
   * similar to upcoming.org and flickr, where the page displays your own tags differently than
   * other users' tags.
   *
   * @param int The unique ID of the object in question.
   * @param int The offset of tags to return.
   * @param int The size of the tagset to return. Use a zero size to get all tags.
   * @param int The unique ID of the person who tagged the object, if user-level tags only are preferred. [FC]
   *
   * @return array Returns a PHP array with object elements ordered by object ID. Each element is an associative
   * array with the following elements:
   *   - 'tag' => Normalized-form tag
   *	 - 'raw_tag' => The raw-form tag
   *	 - 'tagger_id' => The unique ID of the person who tagged the object with this tag.
   */ 
  function get_tags_on_object($object_id, $offset = 0, $limit = 10, $tagger_id = NULL) {
    if (!isset($object_id)) {
      return false;
    }
    // [FC]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    $db = $this->db;
    if ($limit <= 0) {
      $limit_sql = "";
    } else {
      $limit_sql = "LIMIT $offset, $limit";
    }
    $prefix = $this->_table_prefix;
    $sql = "SELECT DISTINCT tag, raw_tag, tagger_id
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE object_id = $object_id
			$tagger_sql
			ORDER BY id ASC
			$limit_sql
			";
    echo 'sql string is' . $sql . '<br>';
    $rs = $db->Execute($sql) or die("Error: $sql");
    echo 'After sql statement<br>';
    $retarr = array();
    while(!$rs->EOF) {
      // [FC]
      /* $retarr[] = array( */
      /*                   'tag' => $rs->fields['tag'], */
      /*                   'raw_tag' => $rs->fields['raw_tag'], */
      /*                   'tagger_id' => $rs->fields['tagger_id'] */
      /*                   ); */
      $retarr[] = array(
                        'tag' => $rs->fields['tag'],
                        'raw_tag' => $rs->fields['raw_tag'],
                        );
      $rs->MoveNext();
    }
    return $retarr;
  }

  /**
   * safe_tag
   *
   * [FC: big changes necessary here]
   *
   * Pass individual tag phrases along with object and person ID's [FC] in order to 
   * set a tag on an object. If the tag in its raw form does not yet exist,
   * this function will create it.
   * Fails transparently on duplicates, and checks for dupes based on the 
   * block_multiuser_tag_on_object constructor param.
   *
   * @param int The unique ID of the person who tagged the object with this tag.
   * @param int The unique ID of the object in question.
   * @param string A raw string from a web form containing tags.
   *
   * @return boolean Returns true if successful, false otherwise. Does not operate as a transaction.
   */ 
  /* function safe_tag($tagger_id, $object_id, $tag) { */
  /*   if (!isset($tagger_id)||!isset($object_id)||!isset($tag)) { */
  /*     die("safe_tag argument missing"); */
  /*     return false; */
  /*   } */
  /*   $db = $this->db; */
  /*   if ($this->_append_to_integer != '' && is_numeric($tag) && intval($tag) == $tag) { */
  /*     // Converts numeric tag "123" to "123_" to facilitate */
  /*     // alphanumeric sorting (otherwise, PHP converts string to */
  /*     // true integer). */
  /*     $tag = preg_replace('/^([0-9]+)$/', "$1".$this->_append_to_integer, $tag);  */
  /*   } */
  /*   $normalized_tag = $db->qstr($this->normalize_tag($tag), get_magic_quotes_gpc()); */
  /*   $tag = $db->qstr($tag, get_magic_quotes_gpc()); */
  /*   $prefix = $this->_table_prefix; */
  /*   // First, check for duplicate of the normalized form of the tag on this object. */
  /*   // Dynamically switch between allowing duplication between users on the constructor param 'block_multiuser_tag_on_object'. */
  /*   // If it's set not to block multiuser tags, then modify the existence */
  /*   // check to look for a tag by this particular user. Otherwise, the following */
  /*   // query will reveal whether that tag exists on that object for ANY user. */
  /*   if ($this->_block_multiuser_tag_on_object == 0) { */
  /*     $tagger_sql = " AND tagger_id = $tagger_id"; */
  /*   } else { */
  /*     $tagger_sql = ''; */
  /*   } */
  /*   $sql = "SELECT COUNT(*) as count  */
  /* 			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id) */
  /* 			WHERE 1  */
  /* 			$tagger_sql */
  /* 			AND object_id = $object_id */
  /* 			AND tag = $normalized_tag */
  /* 			"; */
  /*   $rs = $db->Execute($sql) or die("Syntax Error: $sql"); */
  /*   if ($rs->fields['count'] > 0) { */
  /*     return true; */
  /*   } */
  /*   // Then see if a raw tag in this form exists. */
  /*   $sql = "SELECT id  */
  /* 			FROM ${prefix}freetags  */
  /* 			WHERE raw_tag = $tag */
  /* 			"; */
  /*   $rs = $db->Execute($sql) or die("Syntax Error: $sql"); */
  /*   if (!$rs->EOF) { */
  /*     $tag_id = $rs->fields['id']; */
  /*   } else { */
  /*     // Add new tag!  */
  /*     $sql = "INSERT INTO ${prefix}freetags (tag, raw_tag) VALUES ($normalized_tag, $tag)"; */
  /*     $rs = $db->Execute($sql) or die("Syntax Error: $sql"); */
  /*     $tag_id = $db->Insert_ID(); */
  /*   } */
  /*   if (!($tag_id > 0)) { */
  /*     return false; */
  /*   } */
  /*   $sql = "INSERT INTO ${prefix}freetagged_objects */
  /* 			(tag_id, tagger_id, object_id, tagged_on) */
  /* 			VALUES ($tag_id, $tagger_id, $object_id, NOW()) */
  /* 			"; */
  /*   $rs = $db->Execute($sql) or die("Syntax error: $sql"); */
  /*   return true; */
  /* } */

  function safe_tag($object_id, $tag) {
    if (!isset($object_id)||!isset($tag)) {
      die("safe_tag argument missing");
      return false;
    }
    $db = $this->db;
    if ($this->_append_to_integer != '' && is_numeric($tag) && intval($tag) == $tag) {
      // Converts numeric tag "123" to "123_" to facilitate
      // alphanumeric sorting (otherwise, PHP converts string to
      // true integer).
      $tag = preg_replace('/^([0-9]+)$/', "$1".$this->_append_to_integer, $tag); 
    }
    $normalized_tag = $db->qstr($this->normalize_tag($tag), get_magic_quotes_gpc());
    $tag = $db->qstr($tag, get_magic_quotes_gpc());
    $prefix = $this->_table_prefix;
    // First, check for duplicate of the normalized form of the tag on this object.
    // Dynamically switch between allowing duplication between users on the constructor param 'block_multiuser_tag_on_object'.
    // If it's set not to block multiuser tags, then modify the existence
    // check to look for a tag by this particular user. Otherwise, the following
    // query will reveal whether that tag exists on that object for ANY user.
    // [FC: I'm leaving this on the grounds that block_multiuser_tag . . . will always be 1]
    if ($this->_block_multiuser_tag_on_object == 0) {
      $tagger_sql = " AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = '';
    }
    $sql = "SELECT COUNT(*) as count 
			FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
			WHERE 1 
			AND object_id = $object_id
			AND tag = $normalized_tag
			";
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");
    if ($rs->fields['count'] > 0) {
      return true;
    }
    // Then see if a raw tag in this form exists.
    $sql = "SELECT id 
			FROM ${prefix}freetags 
			WHERE raw_tag = $tag
			";
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");
    if (!$rs->EOF) {
      $tag_id = $rs->fields['id'];
    } else {
      // Add new tag! 
      $sql = "INSERT INTO ${prefix}freetags (tag, raw_tag) VALUES ($normalized_tag, $tag)";
      $rs = $db->Execute($sql) or die("Syntax Error: $sql");
      $tag_id = $db->Insert_ID();
    }
    if (!($tag_id > 0)) {
      return false;
    }
    $sql = "INSERT INTO ${prefix}freetagged_objects
			(tag_id, object_id, tagged_on)
			VALUES ($tag_id, $object_id, NOW())
			";
    $rs = $db->Execute($sql) or die("Syntax error: $sql");
    return true;
  }


  /**
   * normalize_tag
   *
   * This is a utility function used to take a raw tag and convert it to normalized form.
   * Normalized form is essentially lowercased alphanumeric characters only, 
   * with no spaces or special characters.
   *
   * Customize the normalized valid chars with your own set of special characters
   * in regex format within the option 'normalized_valid_chars'. It acts as a filter
   * to let a customized set of characters through.
   * 
   * After the filter is applied, the function also lowercases the characters using strtolower 
   * in the current locale.
   *
   * The default for normalized_valid_chars is a-zA-Z0-9, or english alphanumeric.
   *
   * @param string An individual tag in raw form that should be normalized.
   *
   * @return string Returns the tag in normalized form.
   */ 
  function normalize_tag($tag) {
    if ($this->_normalize_tags) {
      $normalized_valid_chars = $this->_normalized_valid_chars;
      $normalized_tag = preg_replace("/[^$normalized_valid_chars]/", "", $tag);
      return strtolower($normalized_tag);
    } else {
      return $tag;
    }
  }
  /**
   * delete_object_tag
   * [FC: this tto needs considerable rewrite.]
   *
   * Removes a tag from an object. This does not delete the tag itself from
   * the database. Since most applications will only allow a user to delete
   * their own tags, it supports raw-form tags as its tag parameter, because
   * that's what is usually shown to a user for their own tags.
   *
   * @param int The unique ID of the person who tagged the object with this tag.
   * @param int The ID of the object in question.
   * @param string The raw string form of the tag to delete. See above for notes.
   *
   * @return string Returns the tag in normalized form.
   */ 
  /* function delete_object_tag($tagger_id, $object_id, $tag) { */
  /*   if (!isset($tagger_id)||!isset($object_id)||!isset($tag)) { */
  /*     die("delete_object_tag argument missing"); */
  /*     return false; */
  /*   } */
  /*   $db = $this->db; */
  /*   $tag_id = $this->get_raw_tag_id($tag); */
  /*   $prefix = $this->_table_prefix; */
  /*   if ($tag_id > 0) { */
  /*     $sql = "DELETE FROM ${prefix}freetagged_objects */
  /* 				WHERE tagger_id = $tagger_id */
  /* 				AND object_id = $object_id */
  /* 				AND tag_id = $tag_id */
  /* 				LIMIT 1 */
  /* 				";	 */
  /*     $rs = $db->Execute($sql) or die("Syntax Error: $sql");	 */
  /*     return true; */
  /*   } else { */
  /*     return false;	 */
  /*   } */
  /* } */


  function delete_object_tag($object_id, $tag) {
    if (!isset($object_id)||!isset($tag)) {
      die("delete_object_tag argument missing");
      return false;
    }
    $db = $this->db;
    $tag_id = $this->get_raw_tag_id($tag);
    $prefix = $this->_table_prefix;
    if ($tag_id > 0) {
      $sql = "DELETE FROM ${prefix}freetagged_objects
				WHERE object_id = $object_id
				AND tag_id = $tag_id
				LIMIT 1
				";	
      $rs = $db->Execute($sql) or die("Syntax Error: $sql");	
      return true;
    } else {
      return false;	
    }
  }


  /**
   * delete_all_object_tags
   *
   * Removes all tag from an object. This does not
   * delete the tag itself from the database. This is most useful for
   * cleanup, where an item is deleted and all its tags should be wiped out
   * as well.
   *
   * @param int The ID of the object in question.
   *
   * @return boolean Returns true if successful, false otherwise. It will return true if the tagged object does not exist.
   */ 
  function delete_all_object_tags($object_id) {
    $db = $this->db;
    $prefix = $this->_table_prefix;
    if ($object_id > 0) {
      $sql = "DELETE FROM ${prefix}freetagged_objects
				WHERE 
				object_id = $object_id
				";	
      $rs = $db->Execute($sql) or die("Syntax Error: $sql");	
      return true;
    } else {
      return false;	
    }
  }
  /**
   * delete_all_object_tags_for_user
   * [FC: With my user-free approach, this function is no longer needed]
   *
   * Removes all tag from an object for a particular user. This does not
   * delete the tag itself from the database. This is most useful for
   * implementations similar to del.icio.us, where a user is allowed to retag
   * an object from a text box. That way, it becomes a two step operation of
   * deleting all the tags, then retagging with whatever's left in the input.
   *
   * @param int The unique ID of the person who tagged the object with this tag.
   * @param int The ID of the object in question.
   *
   * @return boolean Returns true if successful, false otherwise. It will return true if the tagged object does not exist.
   */ 
  /* function delete_all_object_tags_for_user($tagger_id, $object_id) { */
  /*   if (!isset($tagger_id)||!isset($object_id)) { */
  /*     die("delete_all_object_tags_for_user argument missing"); */
  /*     return false; */
  /*   } */
  /*   $db = $this->db; */
  /*   $prefix = $this->_table_prefix; */
  /*   if ($object_id > 0) { */
  /*     $sql = "DELETE FROM ${prefix}freetagged_objects */
  /* 				WHERE tagger_id = $tagger_id */
  /* 				AND object_id = $object_id */
  /* 				";	 */
  /*     $rs = $db->Execute($sql) or die("Syntax Error: $sql");	 */
  /*     return true; */
  /*   } else { */
  /*     return false;	 */
  /*   } */
  /* } */




  /**
   * get_tag_id
   *
   * Retrieves the unique ID number of a tag based upon its normal form. Actually,
   * using this function is dangerous, because multiple tags can exist with the same
   * normal form, so be careful, because this will only return one, assuming that
   * if you're going by normal form, then the individual tags are interchangeable.
   *
   * @param string The normal form of the tag to fetch.
   *
   * @return string Returns the tag in normalized form.
   */ 
  function get_tag_id($tag) {
    if (!isset($tag)) {
      die("get_tag_id argument missing");
      return false;
    }
    $db = $this->db;
    $prefix = $this->_table_prefix;
    $tag = $db->qstr($tag, get_magic_quotes_gpc());
    $sql = "SELECT id FROM ${prefix}freetags
			WHERE 
			tag = $tag
			LIMIT 1
			";	
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");	
    return $rs->fields['id'];
  }
  /**
   * get_raw_tag_id
   *
   * Retrieves the unique ID number of a tag based upon its raw form. If a single
   * unique record is needed, then use this function instead of get_tag_id, 
   * because raw_tags are unique.
   *
   * @param string The raw string form of the tag to fetch.
   *
   * @return string Returns the tag in normalized form.
   */ 
  function get_raw_tag_id($tag) {
    if (!isset($tag)) {
      die("get_tag_id argument missing");
      return false;
    }
    $db = $this->db;
    $prefix = $this->_table_prefix;
    $tag = $db->qstr($tag, get_magic_quotes_gpc());
    $sql = "SELECT id FROM ${prefix}freetags
			WHERE 
			raw_tag = $tag
			LIMIT 1
			";	
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");	
    return $rs->fields['id'];
  }
  /**
   * tag_object
   * [FC: rewrite, removing tagger_id
   *
   * This function allows you to pass in a string directly from a form, which is then
   * parsed for quoted phrases and special characters, normalized and converted into tags.
   * The tag phrases are then individually sent through the safe_tag() method for processing
   * and the object referenced is set with that tag. 
   *
   * This method has been refactored to automatically look for existing tags and run
   * adds/updates/deletes as appropriate. It also has been refactored to accept comma-separated lists
   * of tagger_id's and objecct_id's to create either duplicate tagings from multiple taggers or 
   * apply the tags to multiple objects. However, a singular tagger_id and object_id still produces
   * the same behavior.
   *
   * @param int A comma-separated list of unique id's of the tagging subject(s).
   * @param int A comma-separated list of unique id's of the object(s) in question.
   * @param string The raw string form of the tag to delete. See above for notes.
   * @param int Whether to skip the update portion for objects that haven't been tagged. (Default: 1)
   *
   * @return string Returns the tag in normalized form.
   */ 
  /* function tag_object($tagger_id_list, $object_id_list, $tag_string, $skip_updates = 1) { */
  /*   if ($tag_string == '') { */
  /*     // If an empty string was passed, just return true, don't die. */
  /*     // die("Empty tag string passed"); */
  /*     return true; */
  /*   } */
  /*   $db = $this->db; */
  /*   // Break up CSL's for tagger id's and object id's */
  /*   $tagger_id_array = split(',', $tagger_id_list); */
  /*   $valid_tagger_id_array = array(); */
  /*   foreach ($tagger_id_array as $id) { */
  /*     if (intval($id) > 0) { */
  /*       $valid_tagger_id_array[] = intval($id); */
  /*     } */
  /*   } */
  /*   if (count($valid_tagger_id_array) == 0) { */
  /*     return true; */
  /*   } */
  /*   $object_id_array = split(',', $object_id_list); */
  /*   $valid_object_id_array = array(); */
  /*   foreach ($object_id_array as $id) { */
  /*     if (intval($id) > 0) { */
  /*       $valid_object_id_array[] = intval($id); */
  /*     } */
  /*   } */
  /*   if (count($valid_object_id_array) == 0) { */
  /*     return true; */
  /*   } */
  /*   $tagArray = $this->_parse_tags($tag_string); */
  /*   foreach ($valid_tagger_id_array as $tagger_id) { */
  /*     foreach ($valid_object_id_array as $object_id) { */
  /*       $oldTags = $this->get_tags_on_object($object_id, 0, 0, $tagger_id); */
  /*       $preserveTags = array(); */
  /*       if (($skip_updates == 0) && (count($oldTags) > 0)) { */
  /*         foreach ($oldTags as $tagItem) { */
  /*           if (!in_array($tagItem['raw_tag'], $tagArray)) { */
  /*             // We need to delete old tags that don't appear in the new parsed string. */
  /*             $this->delete_object_tag($tagger_id, $object_id, $tagItem['raw_tag']); */
  /*           } else { */
  /*             // We need to preserve old tags that appear (to save timestamps) */
  /*             $preserveTags[] = $tagItem['raw_tag']; */
  /*           } */
  /*         } */
  /*       } */
  /*       $newTags = array_diff($tagArray, $preserveTags); */
  /*       $this->_tag_object_array($tagger_id, $object_id, $newTags); */
  /*     } */
  /*   } */
  /*   return true; */
  /* } */

  function tag_object($object_id_list, $tag_string, $skip_updates = 1) {
    if ($tag_string == '') {
      // If an empty string was passed, just return true, don't die.
      // die("Empty tag string passed");
      return true;
    }
    echo 'In tag_object<br>';
    $db = $this->db;
    // Break up CSL's for object id's (only)
    $object_id_array = split(',', $object_id_list);
    $valid_object_id_array = array();
    foreach ($object_id_array as $id) {
      if (intval($id) > 0) {
        $valid_object_id_array[] = intval($id);
      }
    }
    if (count($valid_object_id_array) == 0) {
      return true;
    }
    $tagArray = $this->_parse_tags($tag_string);
    foreach ($valid_object_id_array as $object_id) {
      echo 'working on object number ' . $object_id . '<br>';
      $oldTags = $this->get_tags_on_object($object_id, 0, 0);  //[FC: dropping tagger_id]
      echo 'got old tags for object number ' . $object_id . '<br>';
      $preserveTags = array();
      if (($skip_updates == 0) && (count($oldTags) > 0)) {
        foreach ($oldTags as $tagItem) {
          if (!in_array($tagItem['raw_tag'], $tagArray)) {
            // We need to delete old tags that don't appear in the new parsed string.
            $this->delete_object_tag($object_id, $tagItem['raw_tag']);
          } else {
            // We need to preserve old tags that appear (to save timestamps)
            $preserveTags[] = $tagItem['raw_tag'];
          }
        }
      }
      $newTags = array_diff($tagArray, $preserveTags);
      $this->_tag_object_array($object_id, $newTags);  // [FC: removing tagger_id]
    }
    echo 'Done with tag_object<br>';
    return true;
  }





  /**
   * _tag_object_array
   * [FC: rewrite necessary]
   *
   * Private method to add tags to an object from an array.
   *
   * @param int Unique ID of tagger
   * @param int Unique ID of object
   * @param array Array of tags to add.
   *
   * @return boolean True if successful, false otherwise.
   */ 
  /* function _tag_object_array($tagger_id, $object_id, $tagArray) { */
  /*   foreach($tagArray as $tag) { */
  /*     $tag = trim($tag); */
  /*     if (($tag != '') && (strlen($tag) <= $this->_MAX_TAG_LENGTH)) { */
  /*       if (get_magic_quotes_gpc()) { */
  /*         $tag = addslashes($tag); */
  /*       } */
  /*       $this->safe_tag($tagger_id, $object_id, $tag); */
  /*     } */
  /*   } */
  /*   return true; */
  /* } */

  function _tag_object_array($object_id, $tagArray) {
    foreach($tagArray as $tag) {
      $tag = trim($tag);
      if (($tag != '') && (strlen($tag) <= $this->_MAX_TAG_LENGTH)) {
        if (get_magic_quotes_gpc()) {
          $tag = addslashes($tag);
        }
        $this->safe_tag($object_id, $tag);
      }
    }
    return true;
  }




  /**
   * _parse_tags
   *
   * Private method to parse tags out of a string and into an array.
   *
   * @param string String to parse.
   *
   * @return array Returns an array of the raw "tags" parsed according to the freetag settings.
   */ 
  function _parse_tags($tag_string) {
    $newwords = array();
    if ($tag_string == '') {
      // If the tag string is empty, return the empty set.
      return $newwords;
    }
    # Perform tag parsing
      if (get_magic_quotes_gpc()) {
        $query = stripslashes(trim($tag_string));
      } else {
        $query = trim($tag_string);
      }
    $words = preg_split('/(")/', $query,-1,PREG_SPLIT_NO_EMPTY|PREG_SPLIT_DELIM_CAPTURE);
    $delim = 0;
    foreach ($words as $key => $word)
      {
        if ($word == '"') {
          $delim++;
          continue;
        }
        if (($delim % 2 == 1) && $words[$key - 1] == '"') {
          $newwords[] = $word;
        } else {
          $newwords = array_merge($newwords, preg_split('/\s+/', $word, -1, PREG_SPLIT_NO_EMPTY));
        }
      }
    return $newwords;
  }
  /**
   * get_most_popular_tags
   *
   * This function returns the most popular tags in the freetag system, with
   * offset and limit support for pagination. It also supports restricting to 
   * an individual user. [FC] Call it with no parameters for a list of 25 most popular
   * tags.
   * 
   * @param int The unique ID of the person to restrict results to.
   * @param int The offset of the tag to start at.
   * @param int The number of tags to return in the result set.
   *
   * @return array Returns a PHP array with tags ordered by popularity descending. 
   * Each element is an associative array with the following elements:
   *   - 'tag' => Normalized-form tag
   *	 - 'count' => The number of objects tagged with this tag.
   */ 
  function get_most_popular_tags($tagger_id = NULL, $offset = 0, $limit = 25) {
    $db = $this->db;
    // [FC: tagger is optional, so no rewrite necessary]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    $prefix = $this->_table_prefix;
    $sql = "SELECT tag, COUNT(*) as count
			FROM ${prefix}freetags INNER JOIN ${prefix}freetagged_objects ON (id = tag_id)
			WHERE 1
			$tagger_sql
			GROUP BY tag
			ORDER BY count DESC, tag ASC
			LIMIT $offset, $limit
			";
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");
    $retarr = array();
    while(!$rs->EOF) {
      $retarr[] = array(
                        'tag' => $rs->fields['tag'],
                        'count' => $rs->fields['count']
                        );
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * get_most_recent_objects
   *
   * This function returns the most recent object ids in the
   * freetag system, with offset and limit support for
   * pagination. It also supports restricting to an individual
   * user. [FC] Call it with no parameters for a list of 25 most
   * recent tags.
   *
   * @param int The unique ID of the person to restrict results to.
   * @param string Tag to filter by
   * @param int The offset of the object to start at.
   * @param int The number of object ids to return in the result set.
   *
   * @return array Returns a PHP array with object ids ordered by
   * timestamp descending.
   * Each element is an associative array with the following elements:
   * - 'object_id' => Object id
   * - 'tagged_on' => The timestamp of each object id
   */ 
  function get_most_recent_objects($tagger_id = NULL, $tag = NULL, $offset = 0, $limit = 25) {
    $db = $this->db;
    // [FC: tagger_id is optional, so no rewrite necessary]
    if (isset($tagger_id)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    $prefix = $this->_table_prefix;
    $sql = "";
    if (!$tag) {
      $sql = "SELECT DISTINCT object_id, tagged_on FROM
				${prefix}freetagged_objects
				WHERE 1
				$tagger_sql
				ORDER BY tagged_on DESC
				LIMIT $offset, $limit ";
    } else {
      $tag = $db->qstr($tag, get_magic_quotes_gpc());
      $sql = "SELECT DISTINCT object_id, tagged_on
				FROM ${prefix}freetagged_objects INNER JOIN ${prefix}freetags ON (tag_id = id)
				WHERE tag = $tag
				$tagger_sql
				ORDER BY tagged_on DESC
				LIMIT $offset, $limit ";
    }
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");
    $retarr = array();
    while(!$rs->EOF) {
      $retarr[] = array(
                        'object_id' => $rs->fields['object_id'],
                        'tagged_on' => $rs->fields['tagged_on']
                        );
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * count_tags
   *
   * Returns the total number of tag->object links in the system.
   * It might be useful for pagination at times, but i'm not sure if I actually use
   * this anywhere. Restrict to a person's tagging by using the $tagger_id parameter. [FC]
   * It does NOT include any tags in the system that aren't directly linked
   * to an object.
   *
   * @param int The unique ID of the person to restrict results to.
   *
   * @return int Returns the count 
   */ 
  function count_tags($tagger_id = NULL, $normalized_version = 0) {
    $db = $this->db;
    // [FC: tagger_id optional, so no rewrite necessary]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    if ($normalized_version == 1) {
      $distinct_col = 'tag';
    } else {
      $distinct_col = 'tag_id';
    }
	
    $prefix = $this->_table_prefix;
    $sql = "SELECT COUNT(DISTINCT $distinct_col) as count
			FROM ${prefix}freetags INNER JOIN ${prefix}freetagged_objects ON (id = tag_id)
			WHERE 1
			$tagger_sql
			";
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");
    if (!$rs->EOF) {
      return $rs->fields['count'];
    }
    return false;
  }
  /**
   * get_tag_cloud_html
   *
   * This is a pretty straightforward, flexible method that automatically
   * generates some html that can be dropped in as a tag cloud.
   * It uses explicit font sizes inside of the style attribute of SPAN 
   * elements to accomplish the differently sized objects.
   *
   * It will also link every tag to $tag_page_url, appended with the 
   * normalized form of the tag. You should adapt this value to your own
   * tag detail page's URL.
   *
   * @param int The number of tags to return. (default: 100)
   * @param int The minimum font size in the cloud. (default: 10)
   * @param int The maximum font size in the cloud. (default: 20)
   * @param string The "units" for the font size (i.e. 'px', 'pt', 'em') (default: px)
   * @param string The class to use for all spans in the cloud. (default: cloud_tag)
   * @param string The tag page URL (default: /tag/)
   * @param int Specify starting record (default: 0)
   *
   * @return string Returns an HTML snippet that can be used directly as a tag cloud.
   */ 
  function get_tag_cloud_html($num_tags = 100, $min_font_size = 10, $max_font_size = 20, $font_units = 'px', $span_class = 'cloud_tag', $tag_page_url = '/tag/', $tagger_id = NULL, $offset = 0) {
    //[FC: minimising changes, just leave tagger_id as NULL.]
    $tag_list = $this->get_tag_cloud_tags($num_tags, $tagger_id, $offset);
    // Get the maximum qty of tagged objects in the set
    if (count($tag_list)) {
      $max_qty = max(array_values($tag_list));
      // Get the min qty of tagged objects in the set
      $min_qty = min(array_values($tag_list));
    } else {
      return '';
    }
    // For ever additional tagged object from min to max, we add
    // $step to the font size.
    $spread = $max_qty - $min_qty;
    if (0 == $spread) { // Divide by zero
      $spread = 1;
    }
    $step = ($max_font_size - $min_font_size)/($spread);
    // Since the original tag_list is alphabetically ordered,
    // we can now create the tag cloud by just putting a span
    // on each element, multiplying the diff between min and qty
    // by $step.
    $cloud_html = '';
    $cloud_spans = array();
    foreach ($tag_list as $tag => $qty) {
      $size = $min_font_size + ($qty - $min_qty) * $step;
      $cloud_span[] = '<span class="' . $span_class . '" style="font-size: '. $size . $font_units . '"><a href="'.$tag_page_url . $tag . '">' . htmlspecialchars(stripslashes($tag)) . '</a></span>';
    }
    $cloud_html = join("\n ", $cloud_span);
    return $cloud_html;
  }
  /**
   * get_tag_cloud_tags
   *
   * This is a function built explicitly to set up a page with most popular tags
   * that contains an alphabetically sorted list of tags, which can then be sized
   * or colored by popularity.
   *
   * Also known more popularly as Tag Clouds!
   *
   * Here's the example case: http://upcoming.org/tag/
   *
   * @param int The maximum number of tags to return.
   * @param int The unique ID of the tagger to restrict to (Optional, NULL default)
   * @param int Specify starting record (default: 0)
   *
   * @return array Returns an array where the keys are normalized tags, and the
   * values are numeric quantity of objects tagged with that tag.
   */ 
  function get_tag_cloud_tags($max = 100, $tagger_id = NULL, $offset = 0) {
    $db = $this->db;
    // [FC: no change necessary.]
    if (isset($tagger_id) && ($tagger_id > 0)) {
      $tagger_sql = "AND tagger_id = $tagger_id";
    } else {
      $tagger_sql = "";
    }
    $max = intval($max);
    $offset = intval($offset);
    if ($offset >= 0 && $max >= 0) {
      $limit_sql = " LIMIT $offset, $max ";
    } elseif ($max >= 0) {
      $limit_sql = " LIMIT 0, $max ";
    } else {
      $max = 100;
      $limit_sql = " LIMIT 0, $max ";
    }
    $prefix = $this->_table_prefix;
    $sql = "SELECT tag, COUNT(object_id) AS quantity
			FROM ${prefix}freetags INNER JOIN ${prefix}freetagged_objects
			ON (${prefix}freetags.id = tag_id)
			WHERE 1
			$tagger_sql
			GROUP BY tag
			ORDER BY quantity DESC
			$limit_sql
			";
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");
    $retarr = array();
    while(!$rs->EOF) {
      $retarr[$rs->fields['tag']] = $rs->fields['quantity'];
      $rs->MoveNext();
    }
    ksort($retarr);
    return $retarr;
  }
  /**
   * count_unique_tags
   * An alias to count_tags.
   *
   * @param int The unique ID of the person to restrict results to.
   * @param int Whether to count normalized tags or all raw tags (0 for raw, 1 for normalized, 0 default)
   *
   * @return int Returns the count
   */ 
  function count_unique_tags($tagger_id = NULL, $normalized_version = 0) {
    return $this->count_tags($tagger_id, $normalized_version);
  } 
  /**
   * similar_tags
   *
   * Finds tags that are "similar" or related to the given tag.
   * It does this by looking at the other tags on objects tagged with the tag specified.
   * Confusing? Think of it like e-commerce's "Other users who bought this also bought," 
   * as that's exactly how this works.
   *
   * Returns an empty array if no tag is passed, or if no related tags are found.
   * Hint: You can detect related tags returned with count($retarr > 0)
   *
   * It's important to note that the quantity passed back along with each tag
   * is a measure of the *strength of the relation* between the original tag
   * and the related tag. It measures the number of objects tagged with both
   * the original tag and its related tag.
   *
   * Thanks to Myles Grant for contributing this function!
   *
   * @param string The raw normalized form of the tag to fetch.
   * @param int The maximum number of tags to return.
   * @param int The unique id of a user to restrict the search to. Optional.
   *
   * @return array Returns an array where the keys are normalized tags, and the
   * values are numeric quantity of objects tagged with BOTH tags, sorted by
   * number of occurences of that tag (high to low).
   */ 
  function similar_tags($tag, $max = 100, $tagger_id = NULL) {
    $retarr = array();
    if (!isset($tag)) {
      return $retarr;
    }
    $db = $this->db;
    $tag = $db->qstr($tag, get_magic_quotes_gpc());
    $where_sql = "";
    // [FC: no change.]
    if (isset($tagger_id) && intval($tagger_id) > 0) {
      $tagger_id = intval($tagger_id);
      $where_sql .= " AND o1.tagger_id = $tagger_id AND o2.tagger_id = $tagger_id ";
    }
    // This query was written using a double join for PHP. If you're trying to eke
    // additional performance and are running MySQL 4.X, you might want to try a subselect
    // and compare perf numbers.
    $prefix = $this->_table_prefix;
    $sql = "SELECT t1.tag, COUNT( o1.object_id ) AS quantity
			FROM ${prefix}freetagged_objects o1
			INNER JOIN ${prefix}freetags t1 ON ( t1.id = o1.tag_id )
			INNER JOIN ${prefix}freetagged_objects o2 ON ( o1.object_id = o2.object_id )
			INNER JOIN ${prefix}freetags t2 ON ( t2.id = o2.tag_id )
			WHERE t2.tag = $tag AND t1.tag != $tag
			$where_sql
			GROUP BY o1.tag_id
			ORDER BY quantity DESC
			LIMIT 0, $max
			";
    $rs = $db->Execute($sql) or die("Syntax Error: $sql");
    while(!$rs->EOF) {
      $retarr[$rs->fields['tag']] = $rs->fields['quantity'];
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * similar_objects
   *
   * This method implements a simple ability to find some objects in the database
   * that might be similar to an existing object. It determines this by trying
   * to match other objects that share the same tags.
   *
   * The user of the method has to use a threshold (by default, 1) which specifies
   * how many tags other objects must have in common to match. If the original object 
   * has no tags, then it won't match anything. Matched objects are returned in order
   * of most similar to least similar.
   *
   * The more tags set on a database, the better this method works. Since this
   * is such an expensive operation, it requires a limit to be set via max_objects.
   *
   * @param int The unique ID of the object to find similar objects for.
   * @param int The Threshold of tags that must be found in common (default: 1)
   * @param int The maximum number of similar objects to return (default: 5).
   * @param int Optionally pass a tagger id to restrict similarity to a tagger's view.
   * 
   * @return array Returns a PHP array with matched objects ordered by strength of match descending. 
   * Each element is an associative array with the following elements:
   * - 'strength' => A floating-point strength of match from 0-1.0
   * - 'object_id' => Unique ID of the matched object
   *
   */ 
  function similar_objects($object_id, $threshold = 1, $max_objects = 5, $tagger_id = NULL) {
    // [FC: is tagger_id even used here?]
    $db = $this->db;
    $retarr = array();
    $object_id = intval($object_id);
    $threshold = intval($threshold);
    $max_objects = intval($max_objects);
    if (!isset($object_id) || !($object_id > 0)) {
      return $retarr;
    }
    if ($threshold <= 0) {
      return $retarr;
    }
    if ($max_objects <= 0) {
      return $retarr;
    }
    // Pass in a zero-limit to get all tags.
    $tagItems = $this->get_tags_on_object($object_id, 0, 0);
    $tagArray = array();
    foreach ($tagItems as $tagItem) {
      $tagArray[] = $db->Quote($tagItem['tag']);
    }
    $tagArray = array_unique($tagArray);
    $numTags = count($tagArray);
    if ($numTags == 0) {
      return $retarr; // Return empty set of matches
    }
    $tagList = join(',', $tagArray);
    $prefix = $this->_table_prefix;
    $sql = "SELECT matches.object_id, COUNT( matches.object_id ) AS num_common_tags
			FROM ${prefix}freetagged_objects as matches
			INNER JOIN ${prefix}freetags as tags ON ( tags.id = matches.tag_id )
			WHERE tags.tag IN ($tagList)
			GROUP BY matches.object_id
			HAVING num_common_tags >= $threshold
			ORDER BY num_common_tags DESC
			LIMIT 0, $max_objects
			";
    $rs = $db->Execute($sql) or die("Syntax Error: $sql, Error: " . $db->ErrorMsg());
    while(!$rs->EOF) {
      $retarr[] = array (
                         'object_id' => $rs->fields['object_id'],
                         'strength' => ($rs->fields['num_common_tags'] / $numTags)
                         );
      $rs->MoveNext();
    }
    return $retarr;
  }
  /**
   * Prints debug text if debug is enabled.
   *
   * @param string The text to output
   * @return boolean Always returns true
   */ 
  function debug_text($text) {
    if ($this->_debug) {
      echo "$text<br>\n";
    }
    return true;
  }
}