<?php

require_once 'Swift/Mime/Header/PathHeader.php';

class Swift_Mime_Header_PathHeaderTest extends UnitTestCase
{
  
  public function testSingleAddressCanBeSetAndFetched()
  {
    $header = $this->_getHeader('Return-Path', 'chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
  }
  
  public function testAddressCanBeSetViaSetter()
  {
    $header = $this->_getHeader('Return-Path');
    $header->setAddress('chris@swiftmailer.org');
    $this->assertEqual('chris@swiftmailer.org', $header->getAddress());
  }
  
  public function testAddressMustComplyWithRfc2822()
  {
    try
    {
      $header = $this->_getHeader('Return-Path', 'chr is@swiftmailer.org');
      $this->fail('Address must be valid according to RFC 2822 addr-spec grammar.');
    }
    catch (Exception $e)
    {
      $this->pass();
    }
  }
  
  public function testValueIsAngleAddrWithValidAddress()
  {
    /* -- RFC 2822, 3.6.7.
      return          =       "Return-Path:" path CRLF

      path            =       ([CFWS] "<" ([CFWS] / addr-spec) ">" [CFWS]) /
                              obs-path
     */
    
    $header = $this->_getHeader('Return-Path', 'chris@swiftmailer.org');
    $this->assertEqual('<chris@swiftmailer.org>', $header->getFieldBody());
  }
  
  public function testValueIsEmptyAngleBracketsIfNoAddressSet()
  {
    $header = $this->_getHeader('Return-Path');
    $this->assertEqual('<>', $header->getFieldBody());
  }
  
  public function testToString()
  {
    $header = $this->_getHeader('Return-Path', 'chris@swiftmailer.org');
    $this->assertEqual('Return-Path: <chris@swiftmailer.org>' . "\r\n",
      $header->toString()
      );
  }
  
  // -- Private methods
  
  private function _getHeader($name, $path = null)
  {
    return new Swift_Mime_Header_PathHeader($name, $path);
  }
  
}