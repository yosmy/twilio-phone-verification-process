<?php

namespace Yosmy\Twilio\Phone\Verification\Start;

use Yosmy;
use Yosmy\Phone\Verification;
use Yosmy\Phone\VerificationException;

/**
 * @di\service()
 */
class ExecuteProcess implements Verification\Start\ExecuteProcess
{
    /**
     * @var string
     */
    private $accountSID;

    /**
     * @var string
     */
    private $authToken;

    /**
     * @var string
     */
    private $serviceSid;

    /**
     * @var Yosmy\Http\ExecuteRequest
     */
    private $executeRequest;

    /**
     * @var Yosmy\ReportError
     */
    private $reportError;

    /**
     * @di\arguments({
     *     accountSID: "%twilio_account_sid%",
     *     authToken:  "%twilio_auth_token%",
     *     serviceSid: "%twilio_verify_service_sid%",
     * })
     *
     * @param string                    $accountSID
     * @param string                    $authToken
     * @param string                    $serviceSid
     * @param Yosmy\Http\ExecuteRequest $executeRequest
     * @param Yosmy\ReportError         $reportError
     */
    public function __construct(
        string $accountSID,
        string $authToken,
        string $serviceSid,
        Yosmy\Http\ExecuteRequest $executeRequest,
        Yosmy\ReportError $reportError
    ) {
        $this->accountSID = $accountSID;
        $this->authToken = $authToken;
        $this->serviceSid = $serviceSid;
        $this->executeRequest = $executeRequest;
        $this->reportError = $reportError;
    }

    /**
     * {@inheritDoc}
     */
    public function execute(
        string $prefix,
        string $number,
        string $template
    ) {
        unset($template);

        try {
            $this->executeRequest->execute(
                'POST',
                sprintf('https://verify.twilio.com/v2/Services/%s/Verifications', $this->serviceSid),
                [
                    'auth' => [$this->accountSID, $this->authToken],
                    'form_params' => [
                        'To' => sprintf('+%s%s', $prefix, $number),
                        'Channel' => 'sms',
                        'Locale' => 'es'
                    ]
                ]
            );
        } catch (Yosmy\Http\Exception $e) {
            $this->reportError->report($e);

            throw new VerificationException('Ocurrió un error interno');
        }
    }
}