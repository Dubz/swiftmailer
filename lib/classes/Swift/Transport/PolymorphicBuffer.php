<?php

/*
 Generic IoBuffer implementation from Swift Mailer.
 
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

//@require 'Swift/Transport/IoBuffer.php';

/**
 * A generic IoBuffer implementation supporting remote sockets and local processes.
 * @package Swift
 * @subpackage Transport
 * @author Chris Corbyn
 */
class Swift_Transport_PolymorphicBuffer implements Swift_Transport_IoBuffer
{
  
  /**
   * A primary socket.
   * @var resource
   * @access private
   */
  private $_stream;
  
  /**
   * The input stream.
   * @var resource
   * @access private
   */
  private $_in;
  
  /**
   * The output stream.
   * @var resource
   * @access private
   */
  private $_out;
  
  /**
   * Buffer initialization parameters.
   * @var array
   * @access private
   */
  private $_params = array();
  
  /**
   * Write sequence.
   * @var int
   * @access private
   */
  private $_sequence = 0;
  
  /**
   * Translations performed on data being streamed into the buffer.
   * @var string[]
   * @access private
   */
  private $_translations = array();
  
  /**
   * Perform any initialization needed, using the given $params.
   * Parameters will vary depending upon the type of IoBuffer used.
   * @param array $params
   */
  public function initialize(array $params)
  {
    $this->_params = $params;
    switch ($params['type'])
    {
      case self::TYPE_SOCKET:
      default:
        $this->_establishSocketConnection();
        break;
    }
  }
  
  /**
   * Set an individual param on the buffer (e.g. switching to SSL).
   * @param string $param
   * @param mixed $value
   */
  public function setParam($param, $value)
  {
  }
  
  /**
   * Perform any shutdown logic needed.
   */
  public function terminate()
  {
    if (isset($this->_stream))
    {
      fclose($this->_stream);
      $this->_stream = null;
      $this->_out = null;
      $this->_in = null;
    }
  }
  
  /**
   * Set an array of string replacements which should be made on data written
   * to the buffer.  This could replace LF with CRLF for example.
   * @param string[] $replacements
   */
  public function setWriteTranslations(array $replacements)
  {
    $this->_translations = $replacements;
  }
  
  /**
   * Get a line of output (including any CRLF).
   * The $sequence number comes from any writes and may or may not be used
   * depending upon the implementation.
   * @param int $sequence of last write to scan from
   * @return string
   */
  public function readLine($sequence)
  {
    if (isset($this->_out) && !feof($this->_out))
    {
      $line = fgets($this->_out);
      return $line;
    }
  }
  
  /**
   * Reads $length bytes from the stream into a string and moves the pointer
   * through the stream by $length. If less bytes exist than are requested the
   * remaining bytes are given instead. If no bytes are remaining at all, boolean
   * false is returned.
   * @param int $length
   * @return string
   */
  public function read($length)
  {
    if (isset($this->_out) && !feof($this->_out))
    {
      $ret = fread($this->_out, $length);
      return $ret;
    }
  }
  
  /**
   * Move the internal read pointer to $byteOffset in the stream.
   * @param int $byteOffset
   * @return boolean
   */
  public function setReadPointer($byteOffset)
  {
  }
  
  /**
   * Writes $bytes to the end of the stream.
   * This method returns the sequence ID of the write (i.e. 1 for first, 2 for
   * second, etc etc).
   * @param string $bytes
   * @return int
   */
  public function write($bytes)
  {
    if (isset($this->_in)
      && fwrite($this->_in, str_replace(
        array_keys($this->_translations),
        array_values($this->_translations),
        $bytes
      )))
    {
      return ++$this->_sequence;
    }
  }
  
  /**
   * Flush the contents of the stream (empty it) and set the internal pointer
   * to the beginning.
   */
  public function flushContents()
  {
    if (isset($this->_in))
    {
      fflush($this->_in);
    }
  }
  
  // -- Private methods
  
  /**
   * Establishes a connection to a remote server.
   * @access private
   */
  private function _establishSocketConnection()
  {
    $host = $this->_params['host'];
    if (isset($this->_params['protocol']))
    {
      $host = $this->_params['protocol'] . '://' . $host;
    }
    $timeout = 15;
    if (isset($this->_params['timeout']))
    {
      $timeout = $this->_params['timeout'];
    }
    if (!$this->_stream = fsockopen($host, $this->_params['port'], $errno, $errstr, $timeout))
    {
      throw new Swift_Transport_TransportException(
        'Connection could not be established with host ' . $this->_params['host'] .
        ' [' . $errstr . ' #' . $errno . ']'
        );
    }
    if (!empty($this->_params['blocking']))
    {
      stream_set_blocking($this->_stream, 1);
    }
    else
    {
      stream_set_blocking($this->_stream, 0);
    }
    $this->_in =& $this->_stream;
    $this->_out =& $this->_stream;
  }
  
}