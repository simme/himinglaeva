<?php
// $Id$

/**
 * @file
 * Servers Resource
 * This resource provides simple methods for accessing server information
 * and performing server specific actions.
 */
class ServersResource {

  /**
   * The singleton instance of this resource
   * @access  private
   * @static
   * @var     object
   */
  private static $server;


  // -------------------------------------------------------------------------
  //  Factory C.R.U.D Methods below
  // -------------------------------------------------------------------------

  /**
   * Create a new server
   *
   * @access  public
   * @static
   * @param   object  $data ["data"]
   * @return  object
   */
  public static function create() {

  }

  /**
   * Retrieve the server with the specified ID
   *
   * @access  public
   * @static
   * @param   int     $id   ["path", "0"]
   * @return  object
   */
  public static function retrieve($id) {

  }

  /**
   * Update a server specified by its ID
   *
   * @access  public
   * @static
   * @param   int     $id   ["path", "0"]
   * @param   object  $data ["data"]
   * @return  object
   */
  public static function update($id, $data) {

  }

  /**
   * Delete a server specified by its ID
   *
   * @access  public
   * @static
   * @param   int     $id   ["path", "0"]
   * @return  object
   */
  public static function delete($id) {

  }

}