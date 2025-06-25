<?php
namespace GPay;

abstract class BaseUrl {
    const STAGING = 'https://gpay-staging.libyaguide.net/banking/api/onlinewallet/v1';
    const PRODUCTION = 'https://gpay.ly/banking/api/onlinewallet/v1';
    const DEV = 'http://localhost:8080/banking/api/onlinewallet/v1';
}
