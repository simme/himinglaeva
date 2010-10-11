<?php

/**
 * Custom exception that allows setting the message using printf syntax
 */
class PlatformsResourceException extends Exception {

  /**
   * Modifies the regular exception constructor to let it take a string using
   * printf syntax. Error code must be set using ::setCode()
   *
   * @access  public
   * @param   string    $string
   * @param   mixed     [...]
   */
  public function __construct($string) {
    $args = func_get_args();
    array_shift($args);

    parent::__construct(call_user_func_array('sprintf', $args));
  }

  /**
   * Set the error code
   *
   * @access  public
   * @param   int       $code
   * @return  void
   */
  public function setCode($code) {
    $this->code = (int)$code;
  }

}