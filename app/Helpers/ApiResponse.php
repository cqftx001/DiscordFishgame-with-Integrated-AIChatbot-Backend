<?php
/**
 * Created by PhpStorm.
 * User: macbook
 * Date: 2018/8/24
 * Time: 下午5:57
 */

namespace App\Helpers;

use Symfony\Component\HttpFoundation\Response as FoundationResponse;
use Response;

trait ApiResponse
{
    /**
     * @var int
     */
    protected $statusCode = FoundationResponse::HTTP_OK;

    /**
     * @return mixed
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @param $statusCode
     * @return $this
     */
    public function setStatusCode($statusCode)
    {

        $this->statusCode = $statusCode;
        return $this;
    }

    /**
     * @param $data
     * @param array $header
     * @return mixed
     */
    public function respond($data, $header = [])
    {

        return Response::json($data, $this->getStatusCode(), $header);
    }

    /**
     * @param $status
     * @param array $data
     * @param null $code
     * @return mixed
     */
    public function status($status, array $data, $code = null)
    {

//        if ($code) {
//            $this->setStatusCode($code);
//        }

        return $this->respond([
            'data' => $data ?: new \stdClass(),
            'code' => $this->statusCode
        ]);

    }


    /**
     * @param $status
     * @param array $data
     * @param int $code
     * @return mixed
     */
    public function successStatus($status, array $data, $code = 200)
    {

//        if ($code) {
//            $this->setStatusCode($code);
//        }

        $status = [
            'msg' => $status,
            'code' => $code
        ];

        $data = array_merge($status, $data);
        return $this->respond($data);

    }

    /**
     * @param $message
     * @param int $code
     * @param string $status
     * @return mixed
     */
    public function failed($message, $code = FoundationResponse::HTTP_BAD_REQUEST, $status = 'error')
    {
        return $this->respond([
            'code' => $code,
            'msg' => $message,
            'data' => new \stdClass()
        ]);
    }

    /**
     * @param $message
     * @param string $status
     * @return mixed
     */
    public function message($message, $status = "success")
    {


        return $this->status($status, [
            'msg' => $message
        ]);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function internalError($message = "Internal Error!")
    {

        return $this->failed($message, FoundationResponse::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function created($message = "created")
    {
        return $this->setStatusCode(FoundationResponse::HTTP_CREATED)
            ->message($message);

    }

    /**
     * @param $data
     * @param string $status
     * @return mixed
     */
    public function success($data = [], $code = 200)
    {
        return $this->respond([
            'code' => $code,
            'msg' => 'success',
            'data' => $data ?: [],
        ]);
    }

    /**
     * @param string $message
     * @param int $code
     * @return mixed
     */
    public function returnFailMessage($message = 'error', $code = 0)
    {
        return $this->respond([
            'msg' => $message,
            'code' => $code
        ]);
    }

    /**
     * @param string $message
     * @return mixed
     */
    public function notFond($message = 'Not Fond!')
    {
        return $this->failed($message, Foundationresponse::HTTP_NOT_FOUND);
    }

}
