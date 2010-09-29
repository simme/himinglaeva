<?php
// $Id$

/**
 * @file
 * Platforms Resource
 * This resource provides simple methods for accessing platform information
 * and performing platform specific actions.
 */
class PlatformsResource {

  /**
   * Platform attributes
   */
  private $nid;                   // Platform node ID
  private $name;                  // Platform name
  private $verified;              // Date of last verification, time stamp
  private $publishPath;           // System path to platform
  private $webServer;             // ID of web server this platform is hosted on
  private $release;               // ID of release package
  private $status;                // 1 for enabled, 0 for locked
  private $makefile;              // Platforms makefile


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
   * @Access(callback='PlatformResource::access', args={'create'}, appendArgs=true)
   */
  public static function create() {

  }

  /**
   * Retrieve the platform with the specified ID
   *
   * @access  public
   * @static
   * @param   int     $id   ["path", "0"]
   * @return  object
   *
   * @Access(callback='PlatformResource::access', args={'retrieve'}, appendArgs=true)
   */
  public static function retrieve($id) {

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
   * @Access(callback='PlatformResource::access', args={'update'}, appendArgs=true)
   */
  public static function update($id, $data) {

  }

  /**
   * Delete a platform specified by its ID
   *
   * @access  public
   * @static
   * @param   int     $id   ["path", "0"]
   * @return  object
   *
   * @Access(callback='PlatformResource::access', args={'delete'}, appendArgs=true)
   */
  public static function delete($id) {

  }

  /**
   * Return an index of all the platforms
   *
   * @access  public
   * @static
   * @param   int     $page   ["path", "0"]
   * @param   int     $limit  ["path", "1"]
   * @param   string  $filter ["path", "2"]
   *  All, locked or enabled
   * @return  object
   *
   * @Access(callback='PlatformsResource::access', args={'index'}, appendArgs=true)
   */
  public static function index($page = 0, $limit = 0, $filter = 'all') {
    $platforms = array('hej');

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

  /**
   * Access callback
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