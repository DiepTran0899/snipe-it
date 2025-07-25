<?php

return [

    'undeployable' 		 => 'The following assets cannot be deployed and have been removed from checkout: :asset_tags',
    'does_not_exist' 	 => 'Активът не съществува.',
    'does_not_exist_var' => 'Активът с етике :asset_tag не е намерен.',
    'no_tag' 	         => 'Не е предоставен етикет на актив.',
    'does_not_exist_or_not_requestable' => 'Актива не съществува или не може да бъде предоставян.',
    'assoc_users'	 	 => 'Активът е изписан на потребител и не може да бъде изтрит. Моля впишете го обратно и след това опитайте да го изтриете отново.',
    'warning_audit_date_mismatch' 	=> 'Следващата дата на одит на този актив (:next_audit_date) е преди последната дата на одит (:last_audit_date). Моля, актуализирайте следващата дата на одита.',
    'labels_generated'   => 'Етиката е успешно генериран.',
    'error_generating_labels' => 'Грешка при генериране на етикети.',
    'no_assets_selected' => 'Няма избрани активи.',

    'create' => [
        'error'   		=> 'Активът не беше създаден. Моля опитайте отново.',
        'success' 		=> 'Активът създаден успешно.',
        'success_linked' => 'Артикул с етикет :tag беше създаден успешно. <strong><a href=":link" style="color: white;">Щракнете тук за да го видите</a></strong>.',
        'multi_success_linked' => 'Актив с етикет :links беше създаден успешно.|:count активи бяха създадено успешно. :links.',
        'partial_failure' => 'Грешка при създаване на актив. Съобщението за грешка е: :failures|:count актива не бяха създадени. Съобщението за грешка е: :failures',
        'target_not_found' => [
            'user' => 'The assigned user could not be found.',
            'asset' => 'The assigned asset could not be found.',
            'location' => 'The assigned location could not be found.',
        ],
    ],

    'update' => [
        'error'   			=> 'Активът не беше обновен. Моля опитайте отново.',
        'success' 			=> 'Активът обновен успешно.',
        'encrypted_warning' => 'Активът беше актуализиран успешно, но шифрованите персонализирани полета не бяха актуализирани поради разрешения',
        'nothing_updated'	=>  'Няма избрани полета, съответно нищо не беше обновено.',
        'no_assets_selected'  =>  'Няма избрани активи, така че нищо не бе обновено.',
        'assets_do_not_exist_or_are_invalid' => 'Избраните активи не могат да се обновят.',
    ],

    'restore' => [
        'error'   		=> 'Активът не беше възстановен. Моля опитайте отново.',
        'success' 		=> 'Активът възстановен успешно.',
        'bulk_success' 		=> 'Активът възстановен успешно.',
        'nothing_updated'   => 'Няма избрани активи, така че нищо не бе възстановено.', 
    ],

    'audit' => [
        'error'   		=> 'Одитът на активите е неуспешен: :error ',
        'success' 		=> 'Активният одит бе успешно регистриран.',
    ],


    'deletefile' => [
        'error'   => 'Файлът не беше изтрит. Моля опитайте отново.',
        'success' => 'Файлът изтрит успешно.',
    ],

    'upload' => [
        'error'   => 'Качването неуспешно. Моля опитайте отново.',
        'success' => 'Качването успешно.',
        'nofiles' => 'Не сте избрали файлове за качване или са твърде големи.',
        'invalidfiles' => 'Един или повече файлове са твърде големи или с непозволен тип. Разрешените файлови типове за качване са png, gif, jpg, doc, docx, pdf и txt.',
    ],

    'import' => [
        'import_button'         => 'Импортирай',
        'error'                 => 'Някои елементи не бяха въведени правилно.',
        'errorDetail'           => 'Следните елементи не бяха въведени поради грешки.',
        'success'               => 'Вашият файл беше въведен.',
        'file_delete_success'   => 'Вашият файл беше изтрит успешно.',
        'file_delete_error'      => 'Файлът не е в състояние да бъде изтрит',
        'file_missing' => 'Избраният файл липсва',
        'file_already_deleted' => 'Избрания файл беше вече изтрит',
        'header_row_has_malformed_characters' => 'Един или повече атрибути на заглавния ред съдържат неправилни UTF-8 символи',
        'content_row_has_malformed_characters' => 'Един или повече атрибути на заглавния ред съдържат неправилни UTF-8 символи',
        'transliterate_failure' => 'Транслитерацията от :encoding към UTF-8 беше неуспешна, поради невалидни символи'
    ],


    'delete' => [
        'confirm'   	=> 'Сигурни ли сте, че желаете изтриване на актива?',
        'error'   		=> 'Проблем при изтриване на актива. Моля опитайте отново.',
        'assigned_to_error' => '{1}Актива: :asset_tag е изписан. Впишете го обратно преди изтриване.|[2,*] Активите :asset_tag са изписани. Впишете ги обратно преди изтриване.',
        'nothing_updated'   => 'Няма избрани активи, така че нищо не бе изтрито.',
        'success' 		=> 'Активът е изтрит успешно.',
    ],

    'checkout' => [
        'error'   		=> 'Активът не беше изписан. Моля опитайте отново.',
        'success' 		=> 'Активът изписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
        'not_available' => 'Този актив не е наличен за отписване!',
        'no_assets_selected' => 'Трябва да изберете поне един елемент към списъка',
    ],

    'multi-checkout' => [
        'error'   => 'Актива не беше изписан, моля опитайте отново|Активите не бяха изписани, моля опитайте отново',
        'success' => 'Актива е изписан успешно.|Активите са изписани успешно.',
    ],

    'checkin' => [
        'error'   		=> 'Активът не беше вписан. Моля опитайте отново.',
        'success' 		=> 'Активът вписан успешно.',
        'user_does_not_exist' => 'Невалиден потребител. Моля опитайте отново.',
        'already_checked_in'  => 'Активът е вече вписан.',

    ],

    'requests' => [
        'error'   		=> 'Опитат беше неуспешен, моля опитайте отново.',
        'success' 		=> 'Заявката е успешно подадена.',
        'canceled'      => 'Заявката е отменена.',
        'cancel'        => 'Отмени тази заявка за артикул',
    ],

];
