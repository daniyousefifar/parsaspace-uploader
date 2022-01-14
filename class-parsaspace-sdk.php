<?php

use Goutte\Client;

class ParsaSpace_SDK
{
    protected $client;

    /**
     * Upload EndPoint
     *
     * @var string
     */
    protected $upload_endpoint = 'https://trainbit-upload.parsaspace.com/upload';

    /**
     * Get Link EndPoint
     *
     * @var string
     */
    protected $get_link_endpoint = 'https://parsaspace.com/upload/getlink';

    /**
     * ParsaSpace_SDK class construct
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * Uploader method
     *
     * @param string $file_path
     * @return object
     */
    public function uploader(string $file_path)
    {
        $file_name = basename($file_path);

        $upload = $this->uploadFile($file_path);
        if ($upload->success == true) {
            $result = $this->getLink($upload->file_id);
            $result = $this->generateLink($result->file_id, $file_name);
            $result = $this->generateDownloadLink($result);

            return (object) [
                'success' => true,
                'link' => $result,
            ];
        }

        return (object) [
            'success' => false,
        ];
    }

    /**
     * Generate Link by file id & file name
     *
     * @param int $file_id
     * @param string $file_name
     * @return string
     */
    public function generateLink(int $file_id, string $file_name): string
    {
        $link = 'https://trainbit.com/files/{file_id}/{file_name}';

        $file_name = str_replace(' ', '-', $file_name);

        $link = str_replace('{file_id}', $file_id, $link);
        $link = str_replace('{file_name}', $file_name, $link);

        return $link;
    }

    /**
     * Generate Download Link by URL
     *
     * @param $url
     * @return string
     */
    public function generateDownloadLink($url): string
    {
        $crawler = $this->client->request('GET', $url);
        $form = $crawler->selectButton('تولید لینک دانلود')->form();
        $crawler = $this->client->submit($form);
        return $crawler->filter('.btn.btn-warning.btn-lg')->attr('href');
    }

    /**
     * Get link with file id
     *
     * @param int $file_id
     * @return object
     */
    public function getLink(int $file_id)
    {
        $response = $this->sendRequest($this->getEndpoint('link'), ['fileid' => $file_id], $this->getHeaders());

        if ($response->code == 200 && $response->body->result == 'success') {
            return (object)[
                'success' => true,
                'file_id' => $response->body->fileid,
            ];
        }

        return (object)[
            'success' => false,
        ];
    }

    /**
     * Upload file with file path
     *
     * @param string $file_path
     * @return object
     */
    public function uploadFile(string $file_path)
    {
        $response = $this->sendRequest($this->getEndpoint('upload'), ['file' => new CURLFILE($file_path)], $this->getHeaders());

        if ($response->code == 200 && $response->body->Result == 'success') {
            return (object)[
                'success' => true,
                'file_id' => $response->body->FileId,
            ];
        }

        return (object)[
            'success' => false,
        ];
    }

    /**
     * Request sender
     *
     * @param string $endpoint
     * @param array $data
     * @param array $headers
     * @return object
     */
    protected function sendRequest(string $endpoint, array $data = [], array $headers = [])
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_ENCODING, '');
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_TIMEOUT, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

        $output = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        return (object)[
            'code' => $code,
            'body' => json_decode($output),
        ];
    }

    /**
     * Get Headers
     *
     * @param array $headers
     * @return array
     */
    protected function getHeaders(array $headers = []): array
    {
        $defaultHeaders = [
            'Referer: https://parsaspace.com/upload',
            'Origin: https://parsaspace.com',
            'User-Agent: Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/95.0.4638.69 Safari/537.36'
        ];

        return array_merge($defaultHeaders, $headers);
    }

    /**
     * Get Endpoint by service name
     *
     * @param string $service
     * @return string
     */
    protected function getEndpoint(string $service): string
    {
        switch ($service) {
            case 'upload':
                return $this->upload_endpoint;
            case 'link':
                return $this->get_link_endpoint;
            default:
                return false;
        }
    }
}
