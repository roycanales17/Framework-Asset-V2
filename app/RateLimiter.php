<?php
	
	namespace App;
	
	class RateLimiter
	{
		/**
		 * Attempt to rate limit access based on a given key.
		 *
		 * @param string $key The unique key for the rate-limited resource.
		 * @param int $limit The maximum number of allowed accesses.
		 * @param int $decayRate The time period (in seconds) for which the rate limit is enforced.
		 * @return bool Returns `true` if the access is allowed, `false` otherwise.
		 */
		public static function attempt(string $key, int $limit = 10, int $decayRate = 120): bool {
			
			$ip = IPAddress();
			$cacheKey = "rate:$ip:$key";
			$rateLimit = Cache::get($cacheKey);
			
			if ($rateLimit === false) {
				Cache::set($cacheKey, $limit - 1, $decayRate);
				return true;
			}
			
			if ($rateLimit > 0) {
				$expirationTime = Cache::getExpiration($cacheKey);
				if ($expirationTime !== false) {
					Cache::set($cacheKey, $rateLimit - 1, $expirationTime);
					return true;
				} else {
					return self::attempt($key, $limit, $decayRate);
				}
			}
			
			// If the limit is exceeded, deny the attempt
			return false;
		}
		
		/**
		 * Rate limit access per minute.
		 *
		 * @param string $key The unique key for the rate-limited resource.
		 * @param int $limit The maximum number of allowed accesses per minute.
		 * @return bool Returns `true` if the access is allowed, `false` otherwise.
		 */
		public static function perMinute(string $key, int $limit = 1): bool {
			
			if (self::attempt($key, $limit, 60)) {
				return true;
			}
			
			return false;
		}
		
		/**
		 * Rate limit access per hour.
		 *
		 * @param string $key The unique key for the rate-limited resource.
		 * @param int $limit The maximum number of allowed accesses per hour.
		 * @return bool Returns `true` if the access is allowed, `false` otherwise.
		 */
		public static function perHour(string $key, int $limit = 1): bool {
			
			if (self::attempt($key, $limit, 60 * 60)) {
				return true;
			}
			
			return false;
		}
		
		/**
		 * Rate limit access per day.
		 *
		 * @param string $key The unique key for the rate-limited resource.
		 * @param int $limit The maximum number of allowed accesses per day.
		 * @return bool Returns `true` if the access is allowed, `false` otherwise.
		 */
		public static function perDay(string $key, int $limit = 1): bool {
			
			if (self::attempt($key, $limit, 60 * 60 * 24)) {
				return true;
			}
			
			return false;
		}
		
		/**
		 * Rate limit access per month.
		 *
		 * @param string $key The unique key for the rate-limited resource.
		 * @param int $limit The maximum number of allowed accesses per month.
		 * @return bool Returns `true` if the access is allowed, `false` otherwise.
		 */
		public static function perMonth(string $key, int $limit = 1): bool {
			
			if (self::attempt($key, $limit, 60 * 60 * 24 * 30)) {
				return true;
			}
			
			return false;
		}
	}
