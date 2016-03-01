<?php

namespace SecurePay\XMLAPI\Utils;

/**
 * Commonly used utility functions for the SecurePay XML API
 *
 * Class ApiUtils
 * @author Beng Lim <benglim92@gmail.com>
 * @package SecurePay\XMLAPI\Utils
 */
class ApiUtils
{

    /**
     * Sends a request to a URL
     *
     * @param string $url The url which the request will be sent to
     * @param null|string|array $postdata The post data sent to the server. Can either be empty, a string (post body) or an array for a form post.
     * @param bool $isFormData Indicates whether the post data should be formatted as form data
     * @return string The response from the server
     * @throws \Exception When a cURL exception occurs.
     */
    public static function sendRequest($url, $postdata = null, $isFormData = false) {
        $ch = curl_init();
        $response = null;
        try {
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            if ($postdata != null) {
                curl_setopt($ch, CURLOPT_POST, true);
                if ($isFormData) {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postdata));
                } else {
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
                }
            }

            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, Configurations::getConfig("use_ssl_validation") ? 2 : 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, Configurations::getConfig("use_ssl_validation") ? true : false);
            curl_setopt($ch, CURLOPT_CAINFO, dirname(__FILE__) . "/../../" . Configurations::getConfig("cert_path"));
            $response = curl_exec($ch);
            $curlerr = curl_error($ch);
            if ($curlerr) {
                throw new \Exception($curlerr);
            }
        } finally {
            curl_close($ch);
        }
        return $response;
    }

    /**
     * Generates a random Message Id between a minimum and maximum length.
     *
     * @param int $minlen Minimum length of the Message Id
     * @param int $maxlen Maximum length of the Message Id
     * @return string The message Id
     */
    public static function getRandomMessageId($minlen = 15, $maxlen = 15) {
        $rand = mt_rand($minlen, $maxlen);
        $charsetLen = strlen(Configurations::getConfig("allowed_msg_id_chars"));
        $charSet = Configurations::getConfig("allowed_msg_id_chars"); // workaround for PhpDocumentor
        $retString = "";
        for ($i = 0; $i < $rand; $i++) {
            $retString .= $charSet[mt_rand(0, $charsetLen - 1)];
        }
        return $retString;
    }

    /**
     * Generates an echo XML request.
     *
     * @param $merchantId The merchant Id for authentication
     * @param $txnPassword The transaction password for authentication
     * @return string The echo XML request
     */
    public static function generateEchoRequest($merchantId, $txnPassword) {
        $writer = new \Sabre\Xml\Writer();
        $writer->openMemory();
        $writer->setIndent(Configurations::getConfig("use_indentation"));
        $writer->setIndentString(Configurations::getConfig("indentation_string"));
        $writer->startDocument("1.0", "UTF-8");
        $writer->startElement("SecurePayMessage");
            $writer->startElement("MessageInfo");
                $writer->writeElement("messageID", self::getRandomMessageId());
                $writer->writeElement("messageTimestamp", date("YdmHis000+Z"));
                $writer->writeElement("apiVersion", "xml-4.2");
            $writer->endElement();
            $writer->startElement("MessageInfo");
                $writer->writeElement("merchantID", $merchantId);
                $writer->writeElement("password", $txnPassword);
            $writer->endElement();
            $writer->writeElement("RequestType", "Echo");
        $writer->endElement();
        return $writer->outputMemory();
    }
}