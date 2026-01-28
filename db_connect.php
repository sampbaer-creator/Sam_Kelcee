<?php
class CosmosDB {
    private $host, $key, $db, $coll;

    public function __construct($host, $key, $db, $coll) {
        $this->host = $host;
        $this->key = $key;
        $this->db = $db;
        $this->coll = $coll;
    }

    private function request($verb, $resourceId, $resourceType, $data = null) {
        $date = gmdate('D, d M Y H:i:s T');
        $keyDecoded = base64_decode($this->key);
        $sigString = strtolower("$verb\n$resourceType\n$resourceId\n$date\n\n");
        $sig = base64_encode(hash_hmac('sha256', $sigString, $keyDecoded, true));
        $auth = urlencode("type=master&ver=1.0&sig=$sig");

        $headers = [
            "Authorization: $auth",
            "x-ms-date: $date",
            "x-ms-version: 2018-12-31",
            "Content-Type: application/json"
        ];

        if ($resourceType === 'docs' && $verb === 'POST' && isset($data['query'])) {
            $headers[] = "x-ms-documentdb-isquery: True";
            $headers[] = "Content-Type: application/query+json";
        }

        $ch = curl_init("https://" . parse_url($this->host, PHP_URL_HOST) . "/dbs/{$this->db}/colls/{$this->coll}/$resourceType");
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $verb);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        if ($data) curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        
        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        return ['code' => $code, 'data' => json_decode($response, true)];
    }

    public function createDocument($data) {
        // Assign a unique ID if one doesn't exist
        if (!isset($data['id'])) $data['id'] = uniqid(); 
        return $this->request('POST', "dbs/{$this->db}/colls/{$this->coll}", 'docs', $data);
    }

    public function query($sql) {
        return $this->request('POST', "dbs/{$this->db}/colls/{$this->coll}", 'docs', ['query' => $sql]);
    }
}
?>