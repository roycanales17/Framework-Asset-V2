<?php
	
	namespace app;
	
	use Exception;
	use Memcached;
	
	class Cache
	{
		private static ?object $instance = null;
		
		/**
		 * Get the singleton instance of Memcached.
		 *
		 * @return object|null
		 */
		public static function instance(): ?object
		{
			if (!self::$instance) {
				
				$active = config('MEMCACHE');
				$server = config('MEMCACHE_SERVER_NAME');
				$port = config('MEMCACHE_PORT');
				
				if (!$active || !$server || !$port) {
					return null;
				}
				
				$obj = new Memcached();
				if (!$obj->addServer($server, $port)) {
					throw new Exception('Failed to connect to Memcache server.');
				}
				
				self::$instance = $obj;
			}
			
			return self::$instance;
		}
		
		/**
		 * Check if a key exists in Memcache.
		 *
		 * @param string $key
		 * @return bool
		 */
		public static function has(string $key): bool
		{
			$obj = self::instance();
			if ($obj) {
				return $obj->get($key) !== false;
			}
			
			return false;
		}
		
		/**
		 * Set a value in Memcache.
		 *
		 * @param string $key
		 * @param mixed $value
		 * @param int $expiration
		 * @return bool
		 */
		public static function set(string $key, mixed $value, int $expiration = 0): bool
		{
			$obj = self::instance();
			if ($obj) {
				$result = $obj->set($key, $value, $expiration);
				if (!$result) {
					throw new Exception('Failed to set value in Memcache.');
				}
				return $result;
			}
			
			return false;
		}
		
		/**
		 * Get a value from Memcache.
		 *
		 * @param string $key
		 * @param mixed $default
		 * @return mixed
		 */
		public static function get(string $key, mixed $default = false): mixed
		{
			$obj = self::instance();
			if ($obj) {
				$value = $obj->get($key);
				if ($value === false) {
					throw new Exception('Failed to retrieve value from Memcache.');
				}
				return $value;
			}
			return $default;
		}
		
		/**
		 * Delete a key from Memcache.
		 *
		 * @param string $key
		 * @return bool
		 */
		public static function delete(string $key): bool
		{
			$obj = self::instance();
			if ($obj) {
				$result = $obj->delete($key);
				if (!$result) {
					throw new Exception('Failed to delete value from Memcache.');
				}
				return $result;
			}
			
			return false;
		}
		
		/**
		 * Clear all values from Memcache.
		 *
		 * @return void
		 */
		public static function clear(): void
		{
			self::instance()?->flush();
		}
		
		/**
		 * Fetch all keys and values from Memcache.
		 *
		 * @return array|bool
		 */
		public static function fetchAll(): array|bool
		{
			$obj = self::instance();
			if ($obj) {
				return $obj->getAllKeys();
			}
			
			return false;
		}
	}
