
<?php


class EmailOTP {
    public static function send($email, $otp) {
        $apiKey = 're_AyEV8Rjk_2ozAnf1fsC1eRSiTMubLK2iR';
        $from = 'contact@firstinmarket.in';
        $subject = 'Your OTP Code';
        $body = 'Your OTP code is: ' . $otp;

        $data = [
            'from' => $from,
            'to' => $email,
            'subject' => $subject,
            'html' => $body
        ];

        $ch = curl_init('https://api.resend.com/emails');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Authorization: Bearer ' . $apiKey,
            'Content-Type: application/json'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return $httpCode === 200;
    }
}


?>