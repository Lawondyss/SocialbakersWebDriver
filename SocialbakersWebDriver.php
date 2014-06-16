<?php
/**
 * Customized PHP WebDriver from Chibimagic.
 * Original see on github https://github.com/chibimagic/WebDriver-PHP
 */

class SocialbakersWebDriver extends WebDriver_Driver
{
  /** @var \Logger */
  public $logger;

  /** @var string */
  public $jobName;

  /** @var string */
  public $projectName;

  /** @var string */
  public $projectPath;

  /** @var string */
  public $homeURL;


  public function __construct($serverUrl, $capabilities)
  {
    sleep(5);
    parent::__construct($serverUrl, $capabilities);

    $this->logger = new Logger;
    $this->set_implicit_wait(WebDriver::$ImplicitWaitMS);
  }


  /**
   * @deprecated Property $jobName is public.
   */
  public function setJobName($jobName)
  {
    $this->jobName = $jobName;
  }

  /**
   * @deprecated Property $jobName is public.
   */
  public function getJobName()
  {
    return $this->jobName;
  }


  /**
   * @deprecated Property $projectName is public.
   */
  public function setProjectName($projectName)
  {
    $this->projectName = $projectName;
  }


  /**
   * @deprecated Property $projectName is public.
   */
  public function getProjectName()
  {
    return $this->projectName;
  }


  /**
   * @deprecated Property $projectPath is public.
   */
  public function setProjectPath($projectPath)
  {
    $this->projectPath = $projectPath;
  }


  /**
   * @deprecated Property $projectPath is public.
   */
  public function getProjectPath()
  {
    return $this->projectPath;
  }


  /**
   * @deprecated Property $homeURL is public.
   */
  public function setHomeURL($homeURL)
  {
    $this->homeURL = $homeURL;
  }


  /**
   * @deprecated Property $homeURL is public.
   */
  public function getHomeURL()
  {
    return $this->homeURL;
  }


  public function save_printscreen()
  {
    date_default_timezone_set('Europe/Prague');

    $domain = 'http://jenkins.ccl';
    $directory = '/job/' . $this->jobName . '/ws/' . $this->projectPath;
    $filename = 'printscreens/screenshot' . date('H_i_s__d_m_Y') . '.png';

    $screenshot = $this->get_screenshot();
    file_put_contents($filename, $screenshot);

    $url = $domain . str_replace(' ', '%20', $directory) . $filename;

    return $url;
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::save_printscreen()
   */
  public function savePrintscreen()
  {
    return $this->save_printscreen();
  }


  public function end_of_test()
  {
    PHPUnit_Framework_Assert::assertFalse($this->logger->isFailed(), $this->logger->getFails());
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::end_of_test()
   */
  public function endOfTest()
  {
    $this->end_of_test();
  }


  public function execute($httpType, $relativeUrl, $payload = null)
  {
    try {
      $response = parent::execute($httpType, $relativeUrl, $payload);
    }
    catch (PHPUnit_Framework_Exception $e) {
      if (!isset($response['body'])) {
        $this->logger->warning('Stop failing bypass due to the an error: ' . $e->getMessage());
        $response = 'fail';
      }
      else {
        $responseJson = json_decode(trim($response['body']), true);
        if (!is_null($responseJson) && $responseJson !== 0) {
          $this->logger->warning('Timeout bypass. The command running too long.');
        }
        else {
          $this->logger->failure('Stop failing bypass on check response status. Error: ' . $e->getMessage());
        }
      }
    }

    return $response;
  }


  public function get_text_byLocator($locator)
  {
    return $this->get_element($locator)->get_text();
  }


  public function get_element($locator, $text = '', $withFail = true)
  {
    if ($locator != 'tag name=body')
      $this->logger->info('Get element ' . $text . ' "' . $locator . '".');

    try {
      $element = parent::get_element($locator);
    }
    catch (PHPUnit_Framework_Exception $e) {
      if ($withFail) {
        $this->logger->failure('Was not find element ' . $text . ' "' . $locator . '" on page ' . $this->get_url());
      }
      $element = new SocialbakersWebElement($this, 'unknown', $locator, $this->logger->getLevel(), $text);
    }

    return $element;
  }


  public function get_element_without_fail($locator, $text = '')
  {
    return $this->get_element($locator, $text, false);
  }


  public function get_all_elements($locator, $text = '')
  {
    $elements = array();
    $allElements = parent::get_all_elements($locator);
    foreach ($allElements as $element) {
      $id = $element->get_element_id();
      $elements[] = new SocialbakersWebElement($this, $id, $locator, $this->logger->getLevel(), $text);
    }

    return $elements;
  }


  public function is_element_present($locator, $text = '')
  {
    try {
      $element = $this->get_element_without_fail($locator, $text);
      if ($this->browser !== 'android') {
        $element->describe();
      }
      $this->logger->note(' ... element is present.');
      $isElementPresent = true;
    }
    catch (WebDriver_NoSuchElementException $e) {
      $this->logger->note(' ... element is not present.');
      $isElementPresent = false;
    }

    return $isElementPresent;
  }


  public function load($url)
  {
    $this->logger->info('loading ' . $url);
    parent::load($url);
  }


  public function go_back()
  {
    $this->logger->info('Go back.');
    parent::go_back();
  }


  public function assert_url($expectedUrl)
  {
    $msg = 'Assert URL "' . $expectedUrl . '".';
    $this->logger->info($msg);
    $this->logger->info('On asserted URL is title: ' . $this->get_title());
    sleep(2);
    try {
      parent::assert_url($expectedUrl);
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg .= ' Failed, while the URL is "' . $this->get_url() . '".';
      $this->logger->failure($msg);
    }
  }


  public function assert_title($expectedTitle, $ieHash = '')
  {
    $msg = 'Assert title "' . $expectedTitle . '".';
    $this->logger->info($msg);
    try {
      parent::assert_title($expectedTitle, $ieHash);
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg .= ' Failed, while the title is "' . $this->get_title() . '".';
      $this->logger->failure($msg);
    }
  }


  public function assert_element_present($locator, $text = '', $withFail = true)
  {
    $msg = 'Assert element ' . $text . ' <{' . $locator . '}> is present.';
    $this->logger->info($msg);
    try {
      parent::assert_element_present($locator);
      return true;
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg .= ' Failed on page: ' . $this->get_url();
      if ($withFail) {
        $this->logger->failure($msg);
      }
      else {
        $this->logger->warning($msg);
      }
      return false;
    }
  }


  public function assert_element_present_no_fail($locator, $text = '')
  {
    $this->assert_element_present($locator, $text, false);
  }


  public function assert_element_not_present($locator, $text = '')
  {
    $msg = 'Assert element '. $text .' <{' . $locator . '}> is not present.';
    $this->logger->info($msg);
    try {
      parent::assert_element_not_present($locator);
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg .= ' Failed on page: ' . $this->get_url();
      $this->logger->failure($msg);
    }
  }


  public function assert_string_present($expectedString)
  {
    $msg = 'Assert string present "' . $expectedString . '".';
    $this->logger->info($msg);
    try {
      parent::assert_string_present($expectedString);
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg .= ' Failed on page: ' . $this->get_url();
      $this->logger->failure($msg);
    }
  }


  public function assert_string_not_present($expectedMissingString)
  {
    try {
      parent::assert_string_not_present($expectedMissingString);
    }
    catch (PHPUnit_Framework_Exception $e) {
      $this->logger->failure('Assert string is not present "' . $expectedMissingString . '" failed.');
    }
  }


  public function get_status()
  {
    return $this->execute('POST', '/status');
  }


  public function wait($seconds, $condition = 'false')
  {
    $js = 'return ' . $condition . ';';
    $endTime = time() + $seconds;

    do {
      $result = $this->execute_js_sync($js);
      sleep(1);
    }
    while ($endTime < time() && !$result);

    return (bool)$result;
  }


  public function wait_for_ajax($seconds)
  {
    $startTimer = time();
    $this->wait($seconds, 'jQuery.active');

    $duration = time() - $startTimer;
    $this->logger->info('Wait for AJAX take ' . $duration . ' seconds. Max wait is ' . $seconds . ' seconds.');
  }


  public function ensure_elements_id()
  {
    $js = '
      function guidGenerator() {
        var S4 = function() {
          return (((1+Math.random())*0x10000)|0).toString(16).substring(1);
        };
        return (S4()+S4()+'-'+S4()+'-'+S4()+'-'+S4()+'-'+S4()+S4()+S4());
      }

      var all = document.getElementsByTagName('*');
      for(var i=0; i < all.length; i++) {
        if(!all[i].getAttribute("id")) {
          all[i].setAttribute("id", guidGenerator());
        }
      }
    ';
    $this->execute_js_sync($js);
  }


  public function get_element_html_by_id($id)
  {
    try {
      $js = 'return document.getElementById("' . $id . '").innerHTML;';
      $result = $this->execute_js_sync($js);

      return $result;
    }
    catch (PHPUnit_Framework_Exception $e) {
      $this->logger->failure('Fail in "' . __METHOD__ . '" with ID "' . $id . '".');
    }
  }


  public function get_elements_text_by_parent_id($id)
  {
    try {
      $js = 'return document.getElementById("' . $id . '").textContent;';
      $result = $this->execute_js_sync($js);

      return $result;
    }
    catch (PHPUnit_Framework_Exception $e) {
      $this->logger->failure('Fail in "' . __METHOD__ . '" with ID "' . $id . '".');
    }
  }


  public function assert_text_present_by_id($id, $presentText)
  {
    $this->logger->info('Assert text "' . $presentText . '" is present in element with ID "' . $id . '".');

    $hasText = $this->has_text_by_id($id, $presentText);
    if ($hasText) {
      $this->logger->note(' ... ok.');
    }
    else {
      $msg = 'Text "' . $presentText . '" was not find in element with ID "' . $id . '" on page: ' . $this->get_url();
      $this->logger->failure($msg);
    }

    return $hasText;
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::assert_text_present_by_id()
   */
  public function FindTextById($id, $presentText)
  {
    return $this->assert_text_present_by_id($id, $presentText);
  }


  public function assert_text_not_present_by_id($id, $notPresentText)
  {
    $this->logger->info('Assert text "' . $notPresentText . '" is not present in element with ID "' . $id . '".');

    $hasText = $this->has_text_by_id($id, $notPresentText);
    if (!$hasText) {
      $this->logger->note(' ... ok.');
    }
    else {
      $msg = 'Text "' . $notPresentText . '" was find in element with ID "' . $id . '".';
      $this->logger->failure($msg);
    }

    return !$hasText;
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::assert_text_not_present_by_id()
   */
  public function notFindTextByID($id, $notPresentText)
  {
    return $this->assert_text_not_present_by_id($id, $notPresentText);
  }


  public function assert_text($locator, $presentText)
  {
    $element = $this->get_element($locator);
    $id = $element->get_element_id();

    $hasText = $this->has_text_by_id($id, $presentText);

    return $hasText;
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::assert_text()
   */
  public function findText($locator, $presentText)
  {
    return $this->assert_text($locator, $presentText);
  }


  public function assert_html_by_id($id, $presentHtml)
  {
    $this->logger->info('Assert HTML "' . $presentHtml . '" in element with ID "' . $id . '".');

    $innerHtml = $this->get_element_html_by_id($id);

    $hasHtml = (strpos($innerHtml, $presentHtml) !== false);
    if ($hasHtml) {
      $this->logger->note(' ... ok.');
    }
    else {
      $msg = 'HTML "' . $presentHtml . '" was not find in element with ID "' . $id . '" on page: ' . $this->get_url();
      $this->logger->warning($msg);
    }

    return $hasHtml;
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::assert_html_by_id()
   */
  public function findHTMLByID($id, $presentHtml)
  {
    return $this->assert_html_by_id($id, $presentHtml);
  }


  public function click_with_retry($locator, $text = '')
  {
    try {
      $this->get_element($locator, $text)->click();
    }
    catch (PHPUnit_Framework_Exception $e) {
      $msg =
        'Load page failed by click on "' . $text . '" <{' . $locator . '}> on page ' .
        $this->get_url() . ' with error: ' . $e->getMessage()
      ;
      $this->logger->warning($msg . ' ... next try.');
      try {
        $this->get_element($locator, $text)->click();
        $this->logger->info('Next try was successful');
      }
      catch (PHPUnit_Framework_Exception $e) {
        $this->logger->failure($msg);
      }
    }
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::click_with_retry()
   */
  public function clickWithRetry($locator, $text = '')
  {
    $this->click_with_retry($locator, $text);
  }


  public function assert_element_is_visible($locator, $text = '')
  {
    $isVisible = $this->get_element($locator, $text)->is_visible();
    if ($isVisible) {
      $this->logger->note(' ... is visible.');
    }
    else {
      $this->logger->note(' ... is not visible.');
      $msg = 'Element "' . $text . '" <{' . $locator . '}> is not visible on page: ' . $this->get_url();
      $this->logger->failure($msg);
    }
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::assert_element_is_visible()
   */
  public function assertElementIsVisible($locator, $text = '')
  {
    $this->assert_element_is_visible($locator, $text);
  }


  public function assert_element_is_hidden($locator, $text = '')
  {
    $isHidden = !$this->get_element($locator, $text)->is_visible();
    if ($isHidden) {
      $this->logger->note(' ... is hidden.');
    }
    else {
      $this->logger->note(' ... is not hidden.');
      $msg = 'Element "' . $text . '" <{' . $locator . '}> is not hidden on page: ' . $this->get_url();
      $this->logger->failure($msg);
    }
  }


  /**
   * @deprecated
   * @see SocialbakersWebDriver::assert_element_is_hidden()
   */
  public function assertElementIsHidden($locator, $text = '')
  {
    $this->assert_element_is_hidden($locator, $text);
  }


  protected function has_text_by_id($id, $searchText)
  {
    $textContent = $this->get_elements_text_by_parent_id($id);
    $hasText = (strpos($textContent, $searchText) !== false);

    return $hasText;
  }
}
