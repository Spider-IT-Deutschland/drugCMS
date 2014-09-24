<?php
/**
 * Project: 
 * drugCMS Content Management System
 * 
 * Description: 
 * Class for form field validation
 * 
 * Requirements: 
 * @con_php_req 5.2
 * 
 * 
 * @package    drugCMS Backend classes
 * @version    1.0.0
 * @author     René Mansveld
 * @copyright  Spider IT Deutschland <www.spider-it.de>
 * @link       http://www.spider-it.de
 * @link       http://www.drugcms.org
 * @since      file available since drugCMS release 2.0.2
 * 
 * {@internal 
 *   created 2014-07-17
 * 
 *   $Id$;
 * }}
 * 
 */

class FormValidation {
    
    /**
     * function isExistingEmailAddress()
     * 
     * Checks if the email address exists by asking it's mailserver
     * 
     * @param string $email E-Mail address to validate
     * 
     * @return bool Existance of the given address
     */
    public static function isExistingEmailAddress($email) {
        # Prepare a sender address for validation
        $sender = 'infe@' . 'drugcms.org';
        # Do the validation
        if (!is_array($email)) {
            $email = array($email);
        }
        $oValidator = new SMTP_validateEmail();
        $ret = $oValidator->validate($email, $sender);
        unset($oValidator);
        return $ret[$email[0]];
    }
    
    /**
     * function isExistingUrl()
     * 
     * Checks if the url exists by calling it
     * 
     * @param string $url Url to check
     * 
     * @return bool Existance of the given url
     */
    public static function isExistingUrl($url) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 1);         # get the header
        curl_setopt($ch, CURLOPT_NOBODY, 1);         # and *only* get the header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); # get the response as a string from curl_exec(), rather than echoing it
        curl_setopt($ch, CURLOPT_FRESH_CONNECT, 1);  # don't use a cached version of the url
        if (curl_exec($ch)) {
            curl_close($ch);
            return true;
        }
        return false;
    }
    
    /**
     * function isValidDate()
     * 
     * Checks if the given date matches the given format and the validity of the date.
     * 
     * @param string $date The date to evaluate
     * @param string $format Format of the date.
     *                       Any combination of <i>mm<i>, <i>dd<i>, <i>yyyy<i>
     *                       with single character separator between.
     * 
     * @return bool Validity of the given date
     * 
     * Examples:
     *  <code>
     *      isValidDate('22.22.2222', 'mm.dd.yyyy'); // returns false
     *      isValidDate('11/30/2008', 'mm/dd/yyyy'); // returns true
     *      isValidDate('30-01-2008', 'dd-mm-yyyy'); // returns true
     *      isValidDate('2008 01 30', 'yyyy mm dd'); // returns true
     *  </code>
     */
    public static function isValidDate($date, $format = 'dd.mm.yyyy') {
        if ((strlen($date) >= 6) && (strlen($format) == 10)) {
            # find separator. Remove all other characters from $format
            $separator_only = str_replace(array('m','d','y'),'', $format);
            $separator      = $separator_only[0]; # separator is first character
            if (($separator) && (strlen($separator_only) == 2)) {
                # make regex
                $regexp = str_replace('mm', '(0?[1-9]|1[0-2])', $format);
                $regexp = str_replace('dd', '(0?[1-9]|[1-2][0-9]|3[0-1])', $regexp);
                $regexp = str_replace('yyyy', '(19|20)?[0-9][0-9]', $regexp);
                $regexp = str_replace($separator, "\\" . $separator, $regexp);
                if (($regexp != $date) && (preg_match('/'.$regexp.'\z/', $date))) {
                    # check date
                    $arr    = explode($separator, $date);
                    $day    = $arr[0];
                    $month  = $arr[1];
                    $year   = $arr[2];
                    if (@checkdate($month, $day, $year)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }
    
    /**
     * function isValidEmailAddress()
     * 
     * Checks if the email address has a valid format
     * 
     * @param string $email E-Mail address to validate
     * 
     * @return bool Validity of the given address
     */
    public static function isValidEmailAddress($email) {
        $oValidator = new EmailAddressValidator();
        $ret = $oValidator->check_email_address($email);
        unset($oValidator);
        return $ret;
    }
    
    /**
     * function isValidName()
     * 
     * Checks if the name has a valid format
     * 
     * @param string $name Name to check
     * 
     * @return bool Validity of the given name
     */
    public static function isValidName($name) {
        global $lang;
        
        $oLang = new cApiLanguage($lang);
        $sLang = strtolower($oLang->getField('language', 'code')) . '_' . strtoupper($oLang->getField('country', 'code'));
        unset($oLang);
        setLocale(LC_ALL, $sLang);
        $regex = '/^[[:alpha:]]+$/';
        return preg_match($regex, $name);
    }
    
    /**
     * function isValidNumber()
     * 
     * Checks if the number has a valid format
     * 
     * @param string $number number to check
     * 
     * @return bool Validity of the given number
     */
    public static function isValidNumber($number) {
        $iDot = strpos($number, '.');
        $iComma = strpos($number, ',');
        if (($iDot !== false) && ($iComma !== false) && ($iDot > $iComma)) {
            return is_numeric($number);
        } elseif (($iDot !== false) && ($iComma !== false) && ($iDot < $iComma)) {
            return is_numeric(str_replace(array('.', ','), array('', '.'), $number));
        } elseif ($iDot !== false) {
            return is_numeric($number);
        } elseif ($iComma !== false) {
            return is_numeric(str_replace(',', '.', $number));
        }
    }
    
    /**
     * function isValidPhoneNumber()
     * 
     * Checks if the phone number has a valid format
     * 
     * @param string $phone Phone number to check
     * 
     * @return bool Validity of the given phone number
     */
    public static function isValidPhoneNumber($phone) {
        $regex = '/^(\+[0-9]{2,3}|0+[0-9]{2,5}).+[\d\s\/\(\)-]/';
        return preg_match($regex, $phone);
    }
    
    /**
     * function isValidUrl()
     * 
     * Checks if the url has a valid format
     * 
     * @param string $url Url to check
     * 
     * @return bool Validity of the given url
     */
    public static function isValidUrl($url) {
        $regex = '/\b(?:(?:https?|ftp):\/\/|www\.)[-a-z0-9+&@#\/%?=~_|!:,.;]*[-a-z0-9+&@#\/%=~_|]/i';
        return preg_match($regex, $url);
    }
}
?>