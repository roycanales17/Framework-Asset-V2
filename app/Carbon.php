<?php
	
	namespace app;
	
	use app\Helper\BaseCarbon;
	
	class Carbon
	{
		/**
		 * Get the current date and time.
		 *
		 * @return BaseCarbon
		 */
		public static function now(): BaseCarbon
		{
			return BaseCarbon::now();
		}
		
		/**
		 * Get the current date.
		 *
		 * @return string
		 */
		public static function today(): string
		{
			return BaseCarbon::today()->toDateString();
		}
		
		/**
		 * Add days to the current date and time.
		 *
		 * @param int $days
		 * @return BaseCarbon
		 */
		public static function addDays(int $days): BaseCarbon
		{
			return BaseCarbon::now()->addDays($days);
		}
		
		/**
		 * Subtract days from the current date and time.
		 *
		 * @param int $days
		 * @return BaseCarbon
		 */
		public static function subtractDays(int $days): BaseCarbon
		{
			return BaseCarbon::now()->subDays($days);
		}
		
		/**
		 * Format the current date and time.
		 *
		 * @param string $format
		 * @return string
		 */
		public static function format(string $format = 'Y-m-d H:i:s'): string
		{
			return BaseCarbon::now()->format($format);
		}
		
		/**
		 * Parse a date string into a Carbon instance.
		 *
		 * @param string $date
		 * @return BaseCarbon
		 */
		public static function parse(string $date): BaseCarbon
		{
			return BaseCarbon::parse($date);
		}
		
		/**
		 * Get the difference between two dates in days.
		 *
		 * @param string $date1
		 * @param string $date2
		 * @return int
		 */
		public static function diffInDays(string $date1, string $date2): int
		{
			$carbonDate1 = BaseCarbon::parse($date1);
			$carbonDate2 = BaseCarbon::parse($date2);
			
			return $carbonDate1->diffInDays($carbonDate2);
		}
		
		/**
		 * Check if a date is in the future.
		 *
		 * @param string $date
		 * @return bool
		 */
		public static function isFuture(string $date): bool
		{
			return BaseCarbon::parse($date)->isFuture();
		}
		
		/**
		 * Check if a date is in the past.
		 *
		 * @param string $date
		 * @return bool
		 */
		public static function isPast(string $date): bool
		{
			return BaseCarbon::parse($date)->isPast();
		}
	}
