<?php
namespace Modular\Fields;

use Modular\Application;
use Modular\Exceptions\Exception;
use Modular\Traits\bitfield;
use Modular\Traits\debugging;
use Modular\Traits\emailer;

/**
 * Class which only allows certain transitions for this fields values as defined by config.states. Adds a field
 * to extended module <ClassName>StateUpdated
 *
 * @package Modular\Fields
 */
abstract class StateEngineField extends Enum {
	use emailer;
	use debugging;
	use bitfield;

	// field names for an instance are the actual field name postfixed by one of these e.g. 'QueueStatusUpdatedDate' or 'QueueStatusInitiatedByID'
	// more than one StateEngineField could exist on a model.
	const InitiatedByFieldPostfix = 'InitiatedByID';
	const UpdatedDateFieldPostfix = 'UpdatedDate';
	const UpdatedByFieldPostfix   = 'UpdatedByID';
	const WatcherEmailPostfix     = 'WatcherEmail';

	// extension method is called with this postfixed to the class name, e.g. 'StateChange'
	// with the field name as first parameter and either StateChanging or StateChanged as second parameter
	const StateChangeEventName = 'stateChange';

	const StateChanging = 'Changing';
	const StateChanged  = 'Changed';

	const NotifyEmailSystemAdmin = 1;
	const NotifyEmailAdmin       = 2;
	const NotifyEmailInitiator   = 4;
	const NotifyEmailUpdater     = 4;
	const NotifyEmailWatcher     = 8;

	// set this to an email address and the NotifyEmailWatcher flag to send all transitions for the field type to an email address (can also be set on
	// the extended model as config state_engine_watcher_email for model level granularity)
	private static $watcher_email = '';

	private static $show_as = self::ShowAsDropdown;

	/**
	 * Array of states to array of valid 'next' states.
	 *
	 * @var array
	 */
	private static $options = [
		#   self::State1 => [
		#       self::State2,
		#       self::State4,
		#   ],
		#   self::State2 => [
		#       self::State3,
		#       self::State4
		#   ],
		#   self::State3 => [
		#       self::State2
		#       self::State4
		#   ],
		#   self::State4 => [
		#
		#   ]
	];

	private static $notify_on_state_events = [
		#   self::ToState1 => self::EmailSystemAdmin,
		#   self::ToState2 => self::EmailAdmin,
		#   self::ToState3 => [
		#       self::FromState1 => self::EmailAdmin,
		#       self::FromState2 => self::EmailInitiator
		#   ]
	];

	/**
	 * Adds a StateUpdated DateTime field to the model as well as the parent Enum field.
	 *
	 * @param $mode
	 * @return array
	 */
	public function cmsField($mode = null) {
		$updatedBy = \Member::get()->byID($this()->{static::updated_by_field_name()}) ?: \Member::currentUser();
		$updatedByName = $updatedBy
			? ($updatedBy->FirstName . ' ' . $updatedBy->Surname . ' (' . $updatedBy->Email . ')') : 'Unknown';

		$initiatedBy = \Member::get()->byID($this()->{static::initiated_by_field_name()}) ?: \Member::currentUser();
		$initiatedByName = $initiatedBy
			? ($initiatedBy->FirstName . ' ' . $initiatedBy->Surname . ' (' . $initiatedBy->Email . ')') : 'Unknown';

		$watcherEmails = $this->watcherEmails();

		return array_merge(parent::cmsFields($mode), [
			static::updated_date_field_name()     => $this->configureDateTimeField(new \DatetimeField(static::updated_date_field_name())),
			static::initiated_by_field_name('RO') => new \ReadonlyField(static::initiated_by_field_name('RO'), 'Initiated By', $initiatedByName),
			static::updated_by_field_name('RO')   => new \ReadonlyField(static::updated_by_field_name('RO'), 'Updated By', $updatedByName),
			static::watcher_email_field_name()    => new \EmailField(static::watcher_email_field_name(), 'Watcher Email', current($watcherEmails)),
		]);
	}

	/**
	 * Return configured default_value or key of first option.
	 * @return mixed
	 */
	public static function default_value() {
		return parent::default_value() ?: key(static::options());
	}

	/**
	 * Return an array of states which are allowed to be chosen given the current state. If no current state
	 * then the first configured state is the only option.
	 *
	 * @return array
	 */
	public function optionMap() {
		$options = static::options(
			null,
			array_combine(
				array_keys(static::config()->get('options')),
				array_keys(static::config()->get('options'))
			)
		);

		// only the first configured top-level state is allowed by default if no valid next state is found.
		$next = [key($options) => key($options)];

		if ($this()->isInDB()) {
			if ($current = $this()->{static::field_name()}) {
				if (array_key_exists($current, $options)) {
					// create a map with next options as key and value
					$next = array_combine(
						$options[ $current ],
						$options[ $current ]
					);
				} else {
					// something went wrong, set state to show invalid with no value
					$next = [
						'' => 'INVALID'
					];
				}
			}
		}
		// now see if we can replace the values with lang strings
		$next = array_map(
			function($state) {
				return _t(get_class() . ".$state", $state);
			},
			$next
		);
		return $next;
	}

	/**
	 * Adds <ClassName>StateUpdated field as SS_DateTime.
	 *
	 * @param null $class
	 * @param null $extension
	 * @return array
	 */
	public function extraStatics($class = null, $extension = null) {
		$parent = parent::extraStatics($class, $extension) ?: [];

		return array_merge_recursive(
			[
				'db'      => [
					static::updated_date_field_name() => 'SS_DateTime',
				]
			],
			[
				'has_one' => [
					static::updated_by_field_name() => 'Member',
				],
			],
			$parent
		);
	}

	/**
	 * Return a list of emails set as watchers on the extended model, via model configuration or this fields configuration. Falsish values are filtered out.
	 *
	 * @return array
	 */
	public function watcherEmails() {
		return array_filter([
			$this()->{self::watcher_email_field_name()},
			$this()->config()->get('state_engine_watcher_email'),
			$this->config()->get('watcher_email'),
		]);
	}

	/**
	 * @return string e.g. 'QueueStatusUpdatedDate'
	 */
	public static function updated_date_field_name() {
		return parent::field_name('') . static::UpdatedDateFieldPostfix;
	}

	/**
	 * @return string e.g. 'QueueStatusWatcherEmail'
	 */
	public static function watcher_email_field_name() {
		return parent::field_name('') . static::WatcherEmailPostfix;
	}

	/**
	 * @param string $suffix
	 * @return string e.g. 'QueueStatusUpdatedByID'
	 */
	public static function updated_by_field_name($suffix = '') {
		$postfix = substr(static::UpdatedByFieldPostfix, -2) == 'ID'
			? static::UpdatedByFieldPostfix
			: (static::UpdatedByFieldPostfix . 'ID');

		return parent::field_name('') . $postfix . $suffix;
	}

	/**
	 * @param string $suffix
	 * @return string e.g. 'QueueStatusInitiatedByID'
	 */
	public static function initiated_by_field_name($suffix = '') {
		$postfix = substr(static::InitiatedByFieldPostfix, -2) == 'ID'
			? static::InitiatedByFieldPostfix
			: (static::InitiatedByFieldPostfix . 'ID');

		return parent::field_name('') . $postfix . $suffix;
	}

	/**
	 * Before we write check the state transition, if any, is a valid one via checkStateChange method.
	 *
	 * @throws Exception
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$fieldName = static::field_name();

		// previous value would have been set in parent onBeforeWrite method if there was one.
		if ($this->previousValue($previousValue)) {
			// this checks if state can change, will throw an exception if not
			$this->checkStateChange(self::StateChanging, $previousValue, $this()->{$fieldName});
		}
		if (!$this()->isInDB()) {
			// if no value is set then set to a default
			if (!$this()->hasValue($fieldName)) {
				$this()->{$fieldName} = static::default_value();
			}
			if (!$this()->{static::initiated_by_field_name()}) {
				// set the initiator to the current logged in Member or system admin e.g. for cli
				$member = \Member::currentUser() ?: Application::system_admin();
				$this()->{static::initiated_by_field_name()} = $member->ID;
			}
		} else {
			// set the updater to the current logged in Member or system admin e.g. for cli
			$member = \Member::currentUser() ?: Application::system_admin();
			$this()->{static::updated_by_field_name()} = $member->ID;
		}
	}

	/**
	 * If there was a previous state and so state has changed then trigger a StateChanged event on the extended Model.
	 *
	 * @throws Exception
	 */
	public function onAfterWrite() {
		parent::onAfterWrite();
		if ($this->previousValue($previousValue)) {
			// will throw an exception if can't do it
			$this->checkStateChange(self::StateChanged, $previousValue, $this()->{static::field_name()});
		}
	}

	/**
	 * Call extensions via self.StateChangeEventName with event (self.StatusChanging, self.StatusChanged), the field
	 * name and the from and to status. If any handler returns boolean false (strictly) then an exception is thrown.
	 *
	 * @param string $event     i.e. 'StatusChanging', 'StatusChanged'
	 * @param string $fromState e.g. 'Queued'
	 * @param string $toState   e.g. 'Running'
	 * @return bool
	 * @throws Exception
	 */
	public function checkStateChange($event, $fromState, $toState) {
		$fieldName = get_class($this);

		// check model extensions accept the state change
		$checkResults = $this()->invokeWithExtensions(static::StateChangeEventName, $event, $fieldName, $fromState, $toState) ?: [];

		$states = $this->options();

		// check we have the 'from' state
		$checkResults["Invalid from state '$fromState'"] = $transitions = array_key_exists($fromState, $toState) ? $states[ $fromState ] : false;

		// check the 'to' state exists in the 'from' state transitions
		if ($transitions) {
			$checkResults["Invalid to state '$toState'"] = array_key_exists($toState, $transitions);
		}

		// check the result of canChangeState (may be overridden in implementation to perform additional checks)
		$checkResults["canChangeState check"] = $this->canChangeState($event, $fromState, $toState);

		try {
			// any false (strict checking) in results from extension call will cause a fail and so state change will not be saved
			// any other result will be ignored and state transition will continue
			foreach ($checkResults as $error => $eventResult) {
				// something returned false, we fail
				if (is_bool($eventResult) && !$eventResult) {
					$modelClass = get_class($this());
					throw new Exception("$error for '$event' from '$fromState' to '$toState' on '$modelClass.$fieldName'");
				}
			}
		} catch (\Exception $e) {
			$this->debug_fail($e);
		}

		if ($emails = $this->config()->get('notify_on_state_events')) {
			if (isset($emails[ $toState ])) {
				$actionOrEmailAddress = '';

				if (is_array($emails[ $toState ])) {
					if (isset($emails[ $toState ][ $fromState ])) {
						$actionOrEmailAddress = $emails[ $toState ][ $fromState ];
					}
				} else {
					$actionOrEmailAddress = $emails[ $toState ];
				}
				$this->sendStateChangeNotification($event, $fromState, $toState, $actionOrEmailAddress);
			}
		}
		return true;
	}

	/**
	 * Hook in derived classes for custom checking logic (also see states method as alternative)
	 *
	 * @param $event
	 * @param $fromState
	 * @param $toState
	 * @return bool
	 */
	public function canChangeState($event, $fromState, $toState) {
		return true;
	}

	/**
	 * @param            $event
	 * @param            $fromState
	 * @param            $toState
	 * @param int|string $actionOrRecipientEmailAddress one of the self.NotifyABC constants or an email address to send notification to.
	 */
	public function sendStateChangeNotification($event, $fromState, $toState, $actionOrRecipientEmailAddress) {
		// e.g. 'JobStatus_Changed_Queued_Cancelled' or 'JobStatus_Changing_Running'
		$fieldClass = get_class($this);
		$modelClass = get_class($this());
		$modelName = $this()->i18n_singular_name() ?: $modelClass;
		$model = $this();
		$modelID = $model->ID ?: 'new';
		$initiatedBy = \Member::get()->byID($this()->{static::initiated_by_field_name()});
		$updatedBy = \Member::get()->byID($this()->{static::updated_by_field_name()});

		$sender = \Member::currentUser() ?: \Application::member(\Application::Admin);

		$templates = [
			implode('_', [$fieldClass, $event, $fromState, $toState]),
			implode('_', [get_class($this), $event, $toState]),
		];
		$data = [
			'Model'       => $model,
			'ModelName'   => $modelName,
			'ModelID'     => $modelID,
			'FieldName'   => $fieldClass,
			'Event'       => $event,
			'FromState'   => $fromState,
			'ToState'     => $toState,
			'UpdatedBy'   => $updatedBy,
			'InitiatedBy' => $initiatedBy,
			'Templates'   => implode(',', $templates),
		];
		$subject = _t("$modelClass.$fieldClass.Email.Subject", "$modelName ($model->ID) '$model->Title' $event from $fromState to $toState", $data);
		$noTemplateBody = _t("$fieldClass.$modelName.Email.Body", "$$modelName ($model->ID) '$model->Title' $event from $fromState to $toState", $data);

		if (is_numeric($actionOrRecipientEmailAddress)) {
			// value is one of the self.EmailSystemAdmin, self.EmailAdmin etc constants
			if ($this->testbits($actionOrRecipientEmailAddress, self::NotifyEmailSystemAdmin)) {
				$this->send($sender, \Application::find_admin_email(), $subject, $noTemplateBody, $templates, $data);
			}
			if ($this->testbits($actionOrRecipientEmailAddress, self::NotifyEmailAdmin)) {
				$this->send($sender, \Email::config()->get('admin_email'), $subject, $noTemplateBody, $templates, $data);
			}
			if ($this->testbits($actionOrRecipientEmailAddress, self::NotifyEmailInitiator) && $initiatedBy) {
				$this->send($sender, $initiatedBy->Email, $subject, $noTemplateBody, $templates, $data);
			}
			if ($this->testbits($actionOrRecipientEmailAddress, self::NotifyEmailUpdater) && $updatedBy) {
				$this->send($sender, $updatedBy->Email, $subject, $noTemplateBody, $templates, $data);
			}
		} else {
			$this->send($sender, $actionOrRecipientEmailAddress, $subject, $noTemplateBody, $templates, $data);
		}

	}

	/**
	 * Check that the new state being requested is valid from the current state.
	 *
	 * @param \ValidationResult $result
	 * @return array
	 * @throws \ValidationException
	 */
	public function validate(\ValidationResult $result) {
		$fieldName = static::field_name();

		if ($this()->isChanged($fieldName)) {
			$states = static::config()->get('states');

			$new = $this()->{$fieldName};
			$original = $this()->getChangedFields()[ $fieldName ]['before'];

			if (!in_array($new, $states[ $original ])) {
				$result->error(_t(static::field_name() . '.InvalidTransition', "Can't go from state '$original' to '$new'"));
			}
		}

		return parent::validate($result);
	}
}