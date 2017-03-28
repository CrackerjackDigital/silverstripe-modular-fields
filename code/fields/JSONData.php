<?php
namespace Modular\Fields;

use Modular\TypedField;
use Modular\Types\EncodedType;
use Modular\Types\TextType;
use Modular\Exceptions\TypeException as Exception;

class JSONData extends TypedField implements TextType, EncodedType {
	const Name = 'JSONData';

	const DecodeMethod = 'decode';
	const EncodeMethod = 'encode';

	/**
	 * If the value of the field on the extended model is not a string then convert to json before writing.
	 */
	public function onBeforeWrite() {
		parent::onBeforeWrite();
		$this()->{static::Name} = static::encode( $this()->{static::Name} );
	}

	/**
	 * Return json_decoded value of field on the extended model.
	 *
	 * @param string $typeCast
	 *
	 * @return mixed
	 * @throws \Modular\Exceptions\TypeException
	 */
	public function typedValue( $typeCast = '' ) {
		$value = $this()->{static::Name};

		if ( $typeCast == '' ) {
			// return json as php value
			return static::decode( $value );
		} elseif ( $typeCast == TextType::Type ) {
			// return verbatim
			return $value;
		} else {
			throw new Exception( "Typecast must be 'Text' or 'Encoded' if passed" );
		}
	}

	/**
	 * Return the value json encoded.
	 *
	 * @param $value
	 *
	 * @return string
	 */
	public static function encode( $value ) {
		if ( $value && is_string( $value ) ) {
			// if we're not a javascript array or object or 'null' or number then surround with quotes if not already
			if ( trim( trim( $value ), '{[]}' ) == $value ) {
				if ( ! in_array( $value, [ 'null', 'true', 'false' ] ) && ! is_numeric( $value ) ) {
					$value = '"' . trim( $value, '"' ) . '"';
				}
			}
		} else {
			$value = json_encode( $value, JSON_OBJECT_AS_ARRAY );
		}

		return $value;
	}

	/**
	 * @param $value
	 *
	 * @return array|mixed
	 */
	public static function decode( $value ) {
		return json_decode( $value, true );
	}

}