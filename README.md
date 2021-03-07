# List of requests Api

При неверно указанных данных в запросах будет приходить ответ с Validation-error или Unauthorized, т.к. валидация придусмотрена.

Дамп базы данных можно найти среди файлов. Ее нейм "module2.sql".


	// Запрос 1: Регистрация (POST)  
http://localhost:8000/api/register  
// При отправке запроса необходимо передать объект со следующими свойствами:  
	first_name - обязательное поле, строка  
	last_name - обязательное поле, строка  
	phone - обязательное и уникальное поле, строка  
	document_number - обязательное, строка из 10 цифр (может быть с ведущим нулем)  
	password - обязательное поле, строка


	// Запрос 2: Аутентификация (POST)  
http://localhost:8000/api/login  
// При отправке запроса необходимо передать объект с логином(телефоном) и паролем  
	phone  
	password


	// Запрос 3: Список аэропортов (GET)  
http://localhost:8000/api/airport  
// Запрос на поиск аэропортов по названию города или IATA-коду. Поиск без учета регистра.  
При отправке запроса обязательно нужно передать параметр query, который может содержать одно из следующих значений:
- название города(полное название или часть названия)
- название аэропорта(полное название или часть названия)
- IATA код аэропорта (SVO, KZN и т.д.)  
	Пример:  
	http://localhost:8000/api/airport?query=SVO  
	http://localhost:8000/api/airport?query=Moscow


		// Запрос 4: Поиск рейсов (GET)  
http://localhost:8000/api/flight  
// Нужно передать следующие GET-параметры:  

	from (SVO) - iata-код аэропорта вылета, обязательно, должен существовать  
	to (KZN) - iata-код аэропорта назначения, обязательно, должен существовать  
	date1 (2020-10-01) - дата вылета туда, обязательно, в формате YYYY-MM-DD  
	date2 (2020-10-13) - дата возвращения обратно, не обязательно, в формате YYYY-MM-DD  
	passengers (2) - число пассажиров (от 1 до 8 включительно), обязательно  

// В ответе на запрос появится список найденных рейсов из from в to.  
// Если указана дата возвращения (data2), то в поле data.flights_back будут обратные рейсты (из to в from), а иначе пустой массив.


	// Запрос 5: Оформление бронирования (POST)  
http://localhost:8000/api/booking  
// При оформлении бронирования нужно передать на сервер идентификаторы рейсов из базы данных, даты рейсов (в формте YYYY-MM-DD), а также список пассажиров. Каждый пассажир должен содержать следующие поля:  

	first_name - обязательное поле, строка  
	last_name - обязательно поле, строка  
	birth_date - обязательное поле, дата в формате YYYY-MM-DD  
	document_number - обязательное поле, строка из 10 цифр  

// В ответ на запрос должен прийти уникальный код бронирования  
Пример запроса:  
Body:  
{  
	"flight_from": {  
		   "id": 1,  
		   "date": "2020-09-20"  
	},  
	"flight_back": {  
		   "id": 2,  
		   "date": "2020-09-30"  
	},  
	"passengers": [  
		{  
			   "first_name": "Ivan"  
			   "last_name": "Ivanov"  
			   "birth_date": "1990-02-20"  
			   "document_number": 1234567890   
		},  
		{  
			   "first_name": "Oleg"  
			   "last_name": "Gorbunov"  
			   "birth_date": "1990-03-20"  
			   "document_number": 1224567890   
		}  
	]  
}


	// Запрос 6: Информация о бронировании (GET)  
http://localhost:8000/api/booking/{code}  
// Получить информацию о бронировании можно по коду бронирования,
который необходимо вставить вместе "{code}"


	// Запрос 7: Информация о всех бронирования по токену (GET)  
http://localhost:8000/api/user/booking  
Headers  
- Authorization: Bearer {token}   
// В Postman вставляем в поле Bearer ваш токен, сгенерированный при запросе на Аутентификацию.  
// Поправочка - ваш document_number, указанный при регистрации должен совпадать с document_number одного из пассажиров, указанного в бронировании. Именно по этому полю происходит связь юзера и пассажиров, откуда потом вытаскиваются все бронирования из связанных таблиц.

	// Запрос 8: Информация о пользователе по токену(GET)    
http://localhost:8000/api/user/  
Headers  
- Authorization: Bearer {token}  
// В ответ приходит инфа о пользователе  
	first_name  
	last_name  
	phone  
	document_number  