<?php

/*
 A Path Header in Swift Mailer, such a Return-Path.
 
 This program is free software: you can redistribute it and/or modify
 it under the terms of the GNU General Public License as published by
 the Free Software Foundation, either version 3 of the License, or
 (at your option) any later version.
 
 This program is distributed in the hope that it will be useful,
 but WITHOUT ANY WARRANTY; without even the implied warranty of
 MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 GNU General Public License for more details.

 You should have received a copy of the GNU General Public License
 along with this program.  If not, see <http://www.gnu.org/licenses/>.
 
 */

require_once dirname(__FILE__) . '/StructuredHeader.php';

/**
 * A Path Header in Swift Mailer, such a Return-Path.
 * @package Swift
 * @subpackage Mime
 * @author Chris Corbyn
 */
class Swift_Mime_Header_PathHeader extends Swift_Mime_Header_StructuredHeader
{
  
  /**
   * The address in this Header (if specified).
   * @var string
   * @access private
   */
  private $_address;
  
  /**
   * Creates a new PathHeader with the given $name and $address.
   * @param string $name
   * @param string $address, optional
   */
  public function __construct($name, $address = null)
  {
    parent::__construct($name);
    
    if (!is_null($address))
    {
      $this->setAddress($address);
    }
  }
  
  /**
   * Set the Address which should appear in this Header.
   * @param string $address
   */
  public function setAddress($address)
  {
    if (is_null($address))
    {
      $this->_address = null;
    }
    elseif (preg_match('/^' . $this->getHelper()->getGrammar('addr-spec') . '$/D',
      $address))
    {
      $this->_address = $address;
    }
    else
    {
      throw new Exception(
        'Address set in PathHeader does not comply with addr-spec of RFC 2822.'
        );
    }
    $this->setCachedValue(null);
  }
  
  /**
   * Get the address which is used in this Header (if any).
   * Null is returned if no address is set.
   * @return string
   */
  public function getAddress()
  {
    return $this->_address;
  }
  
  /**
   * Get the string value of the body in this Header.
   * This is not necessarily RFC 2822 compliant since folding white space will
   * not be added at this stage (see {@link toString()} for that).
   * @return string
   * @see toString()
   */
  public function getFieldBody()
  {
    if (!$this->getCachedValue())
    {
      $this->setCachedValue('<' . $this->getAddress() . '>');
    }
    return $this->getCachedValue();
  }
  
}