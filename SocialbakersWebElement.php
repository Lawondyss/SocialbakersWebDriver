<?php
/**
 * Customized PHP WebDriver from Chibimagic.
 * Original see on github https://github.com/chibimagic/WebDriver-PHP
 */

class SocialbakersWebElement extends WebDriver_WebElement
{
  /** @var int */
  protected $logLevel;

  /** @var string */
  protected $text;


  public function __construct($driver, $element_id, $locator, $logLevel = Logger::LEVEL_INFO, $text = '')
  {
    parent::__construct($driver, $element_id, $locator);

    $this->logLevel = $logLevel;
    $this->text = $text;
  }


  public function get_next_element($locator, $text = '')
  {
    $element = parent::get_next_element($locator);
    $id = $element->get_element_id();

    return new self($this->driver, $id, $locator, $this->logLevel, $text);
  }


  public function get_all_next_elements($locator, $text = '')
  {
    $elements = array();
    $nextElements = parent::get_all_next_elements($locator);
    foreach ($nextElements as $element) {
      $id = $element->get_element_id();
      $elements[] = new self($this->driver, $id, $locator, $this->logLevel, $text);
    }

    return $elements;
  }


  public function click()
  {
    if ($this->logLevel === Logger::LEVEL_INFO)
      echo '... click.';

    parent::click();
  }


  public function clear()
  {
    if ($this->logLevel === Logger::LEVEL_INFO)
      echo '... clear.';

    parent::clear();
  }


  public function select()
  {
    if ($this->logLevel === Logger::LEVEL_INFO)
      echo '... select.';

    parent::select();
  }


  public function send_keys($keys)
  {
    if ($this->logLevel === Logger::LEVEL_INFO)
      echo '... send keys "' . $keys . '".';

    parent::send_keys($keys);
  }


  public function assert_text_contains($expected_needle)
  {
    try {
      parent::assert_text_contains($expected_needle);
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg =
        'Asserted text "' . $expected_needle . '" is not present in element ' .
        $this->text . ' with <{' . $this->locator . '}>.'
      ;
      $this->driver->logger->failure($msg);
    }
  }


  public function assert_text_does_not_contain($expected_missing_needle)
  {
    try {
      parent::assert_text_does_not_contain($expected_missing_needle);
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg =
        'Asserted not contain text "' . $expected_missing_needle . '" is present in element ' .
        $this->text . ' with <{' . $this->locator . '}>.'
      ;
      $this->driver->logger->failure($msg);
    }
  }
}
