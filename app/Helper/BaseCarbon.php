<?php
	
	namespace App\Helper;
	
	use DateTime;
	use DateTimeZone;
	use Exception;
	
	class BaseCarbon
	{
		protected DateTime $date;
		
		/**
		 * Constructor to initialize the DateTime object.
		 *
		 * @param string|null $time
		 * @throws Exception
		 */
		public function __construct(?string $time = 'now')
		{
			$this->date = new DateTime($time, new DateTimeZone('UTC'));
		}
		
		/**
		 * Get a new instance with the current date and time.
		 *
		 * @return self
		 * @throws Exception
		 */
		public static function now(): BaseCarbon
		{
			return new self();
		}
		
		/**
		 * Get the current date as a string.
		 *
		 * @return self
		 * @throws Exception
		 */
		public static function today(): BaseCarbon
		{
			$instance = new self();
			$instance->date->setTime(0, 0, 0);
			return $instance;
		}
		
		/**
		 * Add days to the current date.
		 *
		 * @param int $days
		 * @return self
		 */
		public function addDays($days): static
		{
			$this->date->modify("+{$days} days");
			return $this;
		}
		
		/**
		 * Subtract days from the current date.
		 *
		 * @param int $days
		 * @return self
		 */
		public function subDays(int $days): static
		{
			$this->date->modify("-{$days} days");
			return $this;
		}
		
		/**
		 * Add months to the current date.
		 *
		 * @param int $months
		 * @return self
		 */
		public function addMonths(int $months): static
		{
			$this->date->modify("+{$months} months");
			return $this;
		}
		
		/**
		 * Subtract months from the current date.
		 *
		 * @param int $months
		 * @return self
		 */
		public function subMonths(int $months): static
		{
			$this->date->modify("-{$months} months");
			return $this;
		}
		
		/**
		 * Add years to the current date.
		 *
		 * @param int $years
		 * @return self
		 */
		public function addYears(int $years): static
		{
			$this->date->modify("+{$years} years");
			return $this;
		}
		
		/**
		 * Subtract years from the current date.
		 *
		 * @param int $years
		 * @return self
		 */
		public function subYears(int $years): static
		{
			$this->date->modify("-{$years} years");
			return $this;
		}
		
		/**
		 * Format the current date and time.
		 *
		 * @param string $format
		 * @return string
		 */
		public function format(string $format = 'Y-m-d H:i:s'): string
		{
			return $this->date->format($format);
		}
		
		/**
		 * Parse a date string into a Helper instance.
		 *
		 * @param string $date
		 * @return self
		 * @throws Exception
		 */
		public static function parse(string $date): BaseCarbon
		{
			return new self($date);
		}
		
		/**
		 * Get the difference between two dates in days.
		 *
		 * @param self $other
		 * @return int
		 */
		public function diffInDays(self $other): int
		{
			$interval = $this->date->diff($other->date);
			return abs($interval->days);
		}
		
		/**
		 * Get the difference between two dates in months.
		 *
		 * @param self $other
		 * @return int
		 */
		public function diffInMonths(self $other): int
		{
			$interval = $this->date->diff($other->date);
			return abs($interval->m + ($interval->y * 12));
		}
		
		/**
		 * Check if the date is in the future.
		 *
		 * @return bool
		 */
		public function isFuture(): bool
		{
			return $this->date > new DateTime('now', new DateTimeZone('UTC'));
		}
		
		/**
		 * Check if the date is in the past.
		 *
		 * @return bool
		 */
		public function isPast(): bool
		{
			return $this->date < new DateTime('now', new DateTimeZone('UTC'));
		}
		
		/**
		 * Convert the Helper instance to a date string.
		 *
		 * @return string
		 */
		public function toDateString(): string
		{
			return $this->date->format('Y-m-d');
		}
		
		/**
		 * Convert the Helper instance to a time string.
		 *
		 * @return string
		 */
		public function toTimeString(): string
		{
			return $this->date->format('H:i:s');
		}
		
		/**
		 * Get the start of the day for the current date.
		 *
		 * @return self
		 */
		public function startOfDay(): static
		{
			$this->date->setTime(0, 0, 0);
			return $this;
		}
		
		/**
		 * Get the end of the day for the current date.
		 *
		 * @return self
		 */
		public function endOfDay(): static
		{
			$this->date->setTime(23, 59, 59);
			return $this;
		}
	}
