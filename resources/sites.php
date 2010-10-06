<?php
// $Id$

/**
 * @file
 * Site Resource
 * Exposes the Ægir site functionality and let's you create new sites, modify
 * existing sites etc.
 */
class SitesResource {

  // -------------------------------------------------------------------------
  //  C.R.U.D.I Methods below
  // -------------------------------------------------------------------------

  /**
   *  Create a new site
   *
   * @access  public
   * @static
   * @param   object  $data   ["data"]
   * @return  object
   *
   * @Access(callback='SitesResource::access', args={'create'}, appendArgs=true)
   */
  public static function create() {

  }

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