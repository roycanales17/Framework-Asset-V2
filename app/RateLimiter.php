<?php
	
	namespace App;
	
	class RateLimiter
	{
		public static function attempt(string $key, int $limit = 10, int $decayRate = 120): bool {
			
			$ip = IPAddress();
			$cacheKey = "rate-limit-$ip-$key";
			$rateLimit = Cache::get($cacheKey);
			
			if (!$rateLimit) {
				Cache::set($cacheKey, $limit - 1, $decayRate);
				return true;
			}
			
			if ($rateLimit > 0) {
				if ($expirationTime = Cache::getExpiration($cacheKey)) {
					Cache::set($cacheKey, $rateLimit - 1, $expirationTime);
					return true;
				}
			}
			
			// If the limit is exceeded, deny the attempt
			return false;
		}
		
		public static function perMinute(string $key, int $limit = 1): bool {
			
			if (self::attempt($key, $limit, 60)) {
				return true;
			}
			
			return false;
		}
		
		public static function perHour(string $key, int $limit = 1): bool {
			
			if (self::attempt($key, $limit, 60 * 60)) {
				return true;
			}
			
			return false;
		}
		
		public static function perDay(string $key, int $limit = 1): bool {
			
			if (self::attempt($key, $limit, 60 * 60 * 24)) {
				return true;
			}
			
			return false;
		}
	}