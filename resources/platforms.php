<?php
// $Id$

class PlatformsResourceException extends Exception {}

/**
 * @file
 * Platforms Resource
 * This resource provides simple methods for accessing platform information
 * and performing platform specific actions.
 *
 * Todo: return error object with specific error message instead of using
 * services_error?
 *
 * Todo: make class methods throw exceptions and do the error handling
 * in the static methods.
 *
 * @TargetedAction(name='lock', controller='lock')
 * @TargetedAction(name='unlock', controller='unlock')
 */
class PlatformsResource {

  /**
   * Platform attributes
   */
  private $nid;                   // Platform node ID
  private $name;                  // Platform name
  private $verified;              // Date of last verification, timestamp
  private $publishpath;           // System path to platform
  private $makefile;              // Platforms makefile
  private $webserver;             // ID of web server this platform is hosted on
  private $release;               // ID of release package
  private $status;                // 1 for enabled, -1 for locked

  // A loaded platform node object
  private $node;

  /**
   * Construct a new platform object
   *
   * @access  public
   * @param   array     $data
   * @return  object
   */
  public function __construct($data) {
    // Loading of existing platform
    if (!empty($data->nid)) {
      $this->node = node_load($data['nid']);
      if (!$this->node) {
        services_error('Platform not found', 404);
        return;
      }
      if ($this->node->type !== 'platform') {
        services_error('The specified node is not a platform.', 400);
        return;
      }

      $this->setNid($this->node->nid);
      $this->setName($this->node->title);
      $this->verified = (int)$this->node->verified;
      $this->setPublishpath($this->node->publish_path);
      $this->setMakefile($this->node->makefile);
      $this->setWebserver($this->node->web_server);
      $this->release = $this->node->release;
      $this->setStatus($this->node->status);
    }

    // Creating a new platform
    else {
      // Set defaults
      $this->webServer = variable_get('hosting_default_web_server', NULL);
      $this->status    = HOSTING_PLATFORM_ENABLED;
    }

    foreach ($data as $property => $value) {
      $method = 'set' . ucfirst(strtolower($property));
      if (method_exists($this, $method)) {
        $this->$method($value);
      }
    }
  }

  /**
   * Construct a new node object to be saved as a new platform
   *
   * @access  public
   * @return  void
   */
  public function makeNode() {
    global $user;

    $node = array(
      'uid'       => $user->uid,
      'status'    => 1,
      'type'      => 'platform',
      'title'     => $this->name,
      'created'   => time(),
      'validated' => TRUE,

      'publish_path'    => $this->publishpath,
      'makefile'        => $this->makefile,
      'web_server'      => $this->webserver,
      'platform_status' => $this->status,
      'verified'        => $this->verified ? $this->verified : 0,
    );

    if ($this->nid) {
      $node['nid'] = $this->nid;
    }

    $this->node = (object)$node;
  }

  /**
   * Save a node
   *
   * @access public
   * @return bool     $success
   */
  public function saveNode() {
    node_save($this->node);
  }

  // -------------------------------------------------------------------------
  //  Mutators - chainable
  // -------------------------------------------------------------------------

  /**
   * Validate and set nid
   *
   * @access  public
   * @param   int       $nid
   * @return  object    $this
   * @throws  PlatformsResourceException
   */
  public function setNid($nid) {
    if (!preg_match('/^[0-9]+$/', $nid)) {
      throw new PlatformsResourceException('Platform nid must be numeric.');
    }

    $this->nid = (int)$nid;

    return $this;
  }

  /**
   * Validate and set platform name
   *
   * @access  public
   * @param   string    $name
   * @return  object    $this
   * @throws  PlatformsResourceException
   */
  public function setName($name) {
    if (!is_string($name)) {
      throw new PlatformsResourceException('Platform name must be a string.');
    }

    $this->name = $name;

    return $this;
  }

  /**
   * Validate and set publish path
   *
   * @access  public
   * @param   string    $publishPath
   * @return  object    $this
   * @throws  PlatformsResourceException
   */
  public function setPublishpath($publishpath) {
    if (!is_string($publishpath)) {
      throw new PlatformsResourceException('Publish Path must be a string.');
    }

    $this->publishpath = $publishpath;

    return $this;
  }

  /**
   * Validate and set makefile
   *
   * @access  public
   * @param   string    $makefile
   * @return  object    $this
   * @throws  PlatformsResourceException
   */
  public function setMakefile($makefile) {
    // Todo: figure out the best way to make sure this is a valid makefile
    if (!is_string($makefile)) {
      throw new PlatformsResourceException('Makefile must be a string.');
    }

    $this->makefile = $makefile;

    return $this;
  }

  /**
   * Validate and set web server
   *
   * @access  public
   * @param   int       $server
   * @return  object    $this
   * @throws  PlatformsResourceException
   */
  public function setWebserver($server) {
    if (!preg_match('/^[0-9]+$/', $server)) {
      throw new PlatformsResourceException('Web server must be identified by an id.');
    }

    $this->webserver = (int)$server;

    return $this;
  }

  /**
   * Validate and set status
   *
   * @access  public
   * @param   int       $status
   * @return  object    $this
   * @throws  PlatformsResourceException
   */
  public function setStatus($status) {
    $statuses = array(
      HOSTING_PLATFORM_LOCKED,
      HOSTING_PLATFORM_ENABLED,
      HOSTING_PLATFORM_DELETED,
      HOSTING_PLATFORM_QUEUED,
    );

    if (!in_array($status, $statuses)) {
      throw new PlatformsResourceException('Invalid platform status.');
    }

    $this->status = (int)$status;

    return $this;
  }

  // -------------------------------------------------------------------------
  //  Accessors
  // -------------------------------------------------------------------------

  /**
   * Return platform node object
   *
   * @access  public
   * @return  object    $node
   */
  public function getNode() {
    return isset($this->node) ? $this->node : FALSE;
  }

  /**
   * Return the platform nid
   *
   * @access  public
   * @return  int       $nid
   */
  public function getNid() {
    return isset($this->nid) ? $this->nid : FALSE;
  }

  /**
   * Return platform name
   *
   * @access  public
   * @return  string    $name
   */
  public function getName() {
    return isset($this->name) ? $this->name : FALSE;
  }

  /**
   * Return the last time the platform was verified
   *
   * @access  public
   * @return  int       $timestamp
   */
  public function lastVerified() {
    return isset($this->verified) ? $this->verified : FALSE;
  }

  /**
   * Return the system path to the platform
   *
   * @access  public
   * @return  string      $publishPath
   */
  public function getPublishpath() {
    return isset($this->publishpath) ? $this->publishpath : FALSE;
  }

  /**
   * Return the makefile path
   *
   * @access  public
   * @return  string      $makefile
   */
  public function getMakefile() {
    return isset($this->makefile) ? $this->makefile : FALSE;
  }

  /**
   * Return the id of the server this platform is hosted on
   *
   * @access  public
   * @return  int         $serverid
   */
  public function getServer() {
    return isset($this->webserver) ? $this->webserver : FALSE;
  }

  /**
   * Return the package id of this platforms release
   *
   * @access  public
   * @return  int         $releaseid
   */
  public function getRelease() {
    return isset($this->release) ? $this->release : FALSE;
  }

  /**
   * Return the status of this platform
   *
   * @access  public
   * @return  int       $status
   */
  public function getStatus() {
    return isset($this->status) ? $this->status : FALSE;
  }

  // -------------------------------------------------------------------------
  //  C.R.U.D.I Methods below
  // -------------------------------------------------------------------------

  /**
   * Create a new platform
   *
   * @access  public
   * @static
   * @param   object  $data ["data"]
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'create'}, appendArgs=true)
   */
  public static function create($data) {
    $platform = new PlatformsResource($data);

    // Create the platform node
    $platform->makeNode();
    // Todo: create node validate method or let Ægir handle that?
    $platform->saveNode();

    // If the platform was successfully created we should have a nid now
    if ($platform->getNid()) {
      return array('status' => 'success');
    } else {
      services_error('Failed to create platform', 500);
    }
  }

  /**
   * Retrieve the platform with the specified ID
   * Todo: returns the entire platform node. Should probably do
   * some clean up.
   *
   * @access  public
   * @static
   * @param   int     $id   ["path", "0"]
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'retrieve'}, appendArgs=true)
   */
  public static function retrieve($id) {
    $platform = node_load((int)$id);

    if (!is_object($platform) || $platform->type != 'platform') {
      services_error('Platform not found', 404);
      return FALSE;
    }

    return $platform;
  }

  /**
   * Update a platform specified by its ID
   *
   * @access  public
   * @static
   * @param   int     $id   ["path", "0"]
   * @param   object  $data ["data"]
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'update'}, appendArgs=true)
   */
  public static function update($id, $data) {
    $platform = new PlatformsResource(array('nid' => $nid));

    if (isset($data->name)) {
      $platform->setName($data->name);
    }

    if (isset($data->publishpath)) {
      $platform->setPublishpath($data->publishpath);
    }

    if (isset($data->makefile)) {
      $platform->setMakefile($data->makefile);
    }

    $platform->makeNode();
    $platfprm->saveNode();
  }

  /**
   * Delete a platform specified by its ID
   *
   * @access  public
   * @static
   * @param   int     $nid  ["path", "0"]
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'delete'}, appendArgs=true)
   */
  public static function delete($nid) {
    // Just deleting the node won't do it
    // Kickstart a delete task for the selected platform

    // Make sure we're actually deleting a platform
    $nid = db_result("SELECT nid FROM {node} WHERE nid = %d AND type='platform'",
      $nid);

    if (!$nid) {
      services_error('The specified nid does not match any platform.', 404);
      return;
    }

    hosting_add_task($nid, 'delete');
  }

  /**
   * Return an index of all the platforms
   *
   * @access  public
   * @static
   * @param   int     $page   ["param", "page"]
   * @param   int     $limit  ["param", "limit"]
   * @param   string  $filter ["param", "filter"]
   *  All, locked or enabled
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'index'}, appendArgs=true)
   */
  public static function index($page = 0, $limit = 0, $filter = 'all') {
    $platforms = array();

    switch (strtolower($filter)) {
      case 'locked':
        $sql = "SELECT n.nid, n.title, h.status FROM {node} n
                  LEFT JOIN {hosting_platform} h ON n.nid = h.nid
                  WHERE n.type='platform' AND n.status=1
                  AND h.status = %d
                  ORDER BY n.title";
        $status = HOSTING_PLATFORM_LOCKED;
        break;

      case 'enabled':
        $sql = "SELECT n.nid, n.title, h.status FROM {node} n
                  LEFT JOIN {hosting_platform} h ON n.nid = h.nid
                  WHERE n.type='platform' AND n.status=1
                  AND h.status <> %d
                  ORDER BY n.title";
        $status = HOSTING_PLATFORM_LOCKED;
        break;

      default:
        $sql = "SELECT n.nid, n.title, h.status FROM {node} n
                  LEFT JOIN {hosting_platform} h ON n.nid = h.nid
                  WHERE n.type='platform' AND n.status=1
                  AND h.status <> %d
                  ORDER BY n.title";
        $status = HOSTING_PLATFORM_DELETED;
        break;
    }

    if ((int)$limit !== 0) {
      $result = db_query_ranged($sql, $status, ($page + 1) * $limit, $limit);
    } else {
      $result = db_query($sql, $status);
    }

    while ($p = db_fetch_object($result)) {
      $platforms[] = array(
        'id'          => $p->nid,
        'name'        => $p->title,
        'status'      => $p->status,
      );
    }

    return $platforms;
  }

  // -------------------------------------------------------------------------
  //  Actions
  // -------------------------------------------------------------------------

  /**
   * Locks a platform
   *
   * @access  public
   * @static
   * @param   object  $data ["data"]
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'lock'}, appendArgs=true)
   */
  public static function lock($data) {
    // Todo: small query instead of node load to check node type
    $node = node_load(array('nid' => $data));
    if ($node->type !== 'platform') {
      services_error('Specified node is not a platform.' . $node->platform, 404);
    }

    hosting_add_task($node->nid, 'lock');
    return array('status' => 'success');
  }

  /**
   * Unlocks a platform
   *
   * @access  public
   * @static
   * @param   object  $data ["data"]
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'unlock'}, appendArgs=true)
   */
  public static function unlock($data) {
    // Todo: small query instead of node load to check node type
    $node = node_load(array('nid' => $data));
    if ($node->type !== 'platform') {
      services_error('Specified node is not a platform.' . $node->platform, 404);
    }

    hosting_add_task($node->nid, 'unlock');
    return array('status' => 'success');
  }

  // -------------------------------------------------------------------------
  //  Helpers
  // -------------------------------------------------------------------------

  /**
   * Access callback
   * Todo: implement
   *
   * @access  public
   * @static
   * @param   string  $op
   * @param   object  $args
   * @return  bool    $access
   */
  public static function access($op, $args) {
    return TRUE;
  }

}