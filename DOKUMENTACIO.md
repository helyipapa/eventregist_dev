# Event Registration API -  Dokumentáció

## 1. Projekt Áttekintés

Az **Event Registration** egy Laravel-alapú REST API alkalmazás, amely eseményrегisztráció és felhasználókezelés funkciókat biztosít. A system támogatja az eseményadminisztráció, felhasználói regisztrációt, és az eseményekre való feliratkozást.

---

## 2. Base URL és Technológiai Stack

### Base URL
```
http://localhost:8000/api
```

### Technológia Stack
- **Backend**: Laravel 12.0
- **Autentikáció**: Laravel Sanctum (Token-alapú)
- **Adatbázis**: SQL (MySQL/PostgreSQL)
- **Frontend**: Vue.js + Tailwind CSS (Vite)
- **PHP verzió**: 8.2+

---

## 3. Autentikáció és Felhasználókezelés

### 3.1 Felhasználói Szerepkörök

| Szerepkör | Leírás | Jogok |
|-----------|--------|-------|
| **Admin** | Rendszergazda felhasználó | Teljes körű hozzáférés minden funkcióhoz |
| **User** | Normál felhasználó | Eseményekre való feliratkozás, saját adatok módosítása |

### 3.2 Felhasználó Szuktura (Users Tábla)

| Oszlop | Típus | Kötelező | Leírás |
|--------|-------|----------|--------|
| `id` | INT (PK) | Igen | Egyedi azonosító |
| `name` | VARCHAR(255) | Igen | Felhasználó neve |
| `email` | VARCHAR(255) | Igen | Email cím (egyedi) |
| `email_verified_at` | TIMESTAMP | Nem | Email megerősítésének ideje |
| `phone` | VARCHAR(20) | Nem | Telefonszám |
| `is_admin` | BOOLEAN | Nem | Admin jogosultság (alapértelmezett: false) |
| `password` | VARCHAR(255) | Igen | Jelszó (titkosított) |
| `remember_token` | VARCHAR(100) | Nem | Emlékeztetési token |
| `created_at` | TIMESTAMP | Igen | Létrehozás dátuma |
| `updated_at` | TIMESTAMP | Igen | Utolsó módosítás dátuma |
| `deleted_at` | TIMESTAMP | Nem | Törlés dátuma (soft delete) |

---

## 4. Adatbázis Terv

```
┌─────────────────────┐         ┌──────────────────┐        ┌──────────────────┐
│ users              │         │ events           │        │ registrations    │
├─────────────────────┤         ├──────────────────┤        ├──────────────────┤
│ id (PK)            │         │ id (PK)          │        │ id (PK)          │
│ name               │         │ title            │        │ user_id (FK)  ──→│─→ users
│ email (unique)     │         │ description      │        │ event_id (FK) ──→│─→ events
│ password           │         │ date             │        │ status           │
│ phone              │         │ location         │        │ registered_at    │
│ is_admin           │         │ max_attendees    │        │ created_at       │
│ deleted_at         │         │ created_at       │        │ updated_at       │
│ created_at         │         │ updated_at       │        │ deleted_at       │
│ updated_at         │         │ deleted_at       │        │ (unique: user_id,│
│ remember_token     │         │                  │        │  event_id)       │
└─────────────────────┘         └──────────────────┘        └──────────────────┘

Kapcsolatok:
- User ↔ Event: Many-to-Many (registrations táblán keresztül)
- User → Registration: One-to-Many
- Event → Registration: One-to-Many
```

---

## 5. Model Struktura

### 5.1 User Model

```php
class User extends Authenticatable {
    protected $fillable = ['name', 'email', 'password', 'phone', 'is_admin'];
    
    // Relációk
    public function registrations() -> hasMany(Registration::class)
    public function events() -> belongsToMany(Event::class)
}
```

**Attribútumok**: name, email, password, phone, is_admin, created_at, updated_at

### 5.2 Event Model

```php
class Event extends Model {
    protected $fillable = ['title', 'date', 'location', 'description', 'max_attendees'];
    
    // Relációk
    public function registrations() -> hasMany(Registration::class)
    public function users() -> belongsToMany(User::class)
}
```

**Attribútumok**: title, date, location, description, max_attendees, created_at, updated_at

### 5.3 Registration Model

```php
class Registration extends Model {
    protected $fillable = ['user_id', 'event_id', 'status', 'registered_at'];
    
    // Relációk
    public function user() -> belongsTo(User::class)
    public function event() -> belongsTo(Event::class)
}
```

**Attribútumok**: user_id, event_id, status, registered_at, created_at, updated_at

**Status értékek**: `függőben`, `elfogadva`, `elutasítva`

---

## 6. API Végpontok

### 6.1 NEM Védett Végpontok (Public)

#### 1. API Health Check
```
GET /ping
```
**Leírás**: API működésének ellenőrzése
**Válasz**:
```json
{
    "message": "API működik"
}
```
**HTTP Státusz**: 200 OK

#### 2. Felhasználó Regisztrációja
```
POST /register
```
**Leírás**: Új felhasználó regisztrációja
**Request Body**:
```json
{
    "name": "Kovács János",
    "email": "kovacs@example.com",
    "password": "jelszó123",
    "phone": "+36301234567"
}
```
**Validáció**:
- `name`: Kötelező, string, max 255 karakter
- `email`: Kötelező, érvényes email, egyedi
- `password`: Kötelező, string, minimum 6 karakter
- `phone`: Opcionális, string, max 20 karakter

**Sikeres Válasz** (201 Created):
```json
{
    "message": "User created successfully",
    "user": {
        "id": 1,
        "name": "Kovács János",
        "email": "kovacs@example.com",
        "phone": "+36301234567"
    }
}
```

**Hiba Válasz** (422 Unprocessable Entity):
```json
{
    "message": "Failed to register user",
    "errors": {
        "email": ["The email has already been taken."],
        "password": ["The password must be at least 6 characters."]
    }
}
```

#### 3. Bejelentkezés
```
POST /login
```
**Leírás**: Felhasználó bejelentkezése és token generálása
**Request Body**:
```json
{
    "email": "kovacs@example.com",
    "password": "jelszó123"
}
```
**Sikeres Válasz** (200 OK):
```json
{
    "message": "Login successful",
    "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
    "user": {
        "id": 1,
        "name": "Kovács János",
        "email": "kovacs@example.com",
        "is_admin": false
    }
}
```

**Hiba Válasz** (401 Unauthorized):
```json
{
    "message": "Invalid credentials"
}
```

---

### 6.2 VÉDETT Végpontok (Authentication szükséges)

> **Megjegyzés**: Minden védett végponthoz szükséges az `Authorization` header:
> ```
> Authorization: Bearer {token}
> ```

#### Felhasználói Végpontok

##### 1. Saját Profil Lekérése
```
GET /me
```
**Autentikáció**: Szükséges
**Leírás**: Az aktuális bejelentkezett felhasználó adatainak lekérése
**Válasz** (200 OK):
```json
{
    "id": 1,
    "name": "Kovács János",
    "email": "kovacs@example.com",
    "phone": "+36301234567",
    "is_admin": false,
    "created_at": "2026-01-14T10:30:00.000000Z",
    "updated_at": "2026-01-14T10:30:00.000000Z"
}
```

##### 2. Saját Profil Frissítése
```
PUT /me
```
**Autentikáció**: Szükséges
**Leírás**: Az aktuális felhasználó adatainak módosítása
**Request Body**:
```json
{
    "name": "Módosított Név",
    "phone": "+36309876543"
}
```
**Válasz** (200 OK):
```json
{
    "message": "Profile updated successfully",
    "user": { ... }
}
```

##### 3. Kijelentkezés
```
POST /logout
```
**Autentikáció**: Szükséges
**Leírás**: Felhasználó kijelentkezése (token invalidálása)
**Válasz** (200 OK):
```json
{
    "message": "Logged out successfully"
}
```

##### 4. Felhasználók Listázása (Admin)
```
GET /users
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Az összes felhasználó listája
**Válasz** (200 OK):
```json
[
    {
        "id": 1,
        "name": "Kovács János",
        "email": "kovacs@example.com",
        "phone": "+36301234567",
        "is_admin": true,
        "created_at": "2026-01-14T10:30:00.000000Z"
    },
    { ... }
]
```

##### 5. Felhasználó Lekérése ID Alapján
```
GET /users/{id}
```
**Autentikáció**: Szükséges
**Leírás**: Specifikus felhasználó adatainak lekérése
**Paraméter**: `id` - Felhasználó ID
**Válasz** (200 OK):
```json
{
    "id": 1,
    "name": "Kovács János",
    "email": "kovacs@example.com",
    ...
}
```

**Hiba Válasz** (404 Not Found):
```json
{
    "message": "User not found"
}
```

##### 6. Felhasználó Létrehozása (Admin)
```
POST /users
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Új felhasználó létrehozása adminisztrátorként
**Request Body**:
```json
{
    "name": "Új Felhasználó",
    "email": "uj@example.com",
    "password": "jelszó123",
    "is_admin": false
}
```
**Válasz** (201 Created): Létrehozott felhasználó adatai

##### 7. Felhasználó Módosítása (Admin)
```
PUT /users/{id}
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Felhasználó adatainak módosítása
**Request Body**:
```json
{
    "name": "Módosított Név",
    "is_admin": true
}
```

##### 8. Felhasználó Törlése (Admin)
```
DELETE /users/{id}
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Felhasználó törlése (soft delete)
**Válasz** (200 OK):
```json
{
    "message": "User deleted successfully"
}
```

#### Esemény Végpontok

##### 1. Események Listázása
```
GET /events
```
**Autentikáció**: Szükséges
**Leírás**: Az összes esemény listája
**Query Paraméterek**:
- `page`: Lapozás (opcionális)
- `per_page`: Elemek száma oldalanként (opcionális)

**Válasz** (200 OK):
```json
[
    {
        "id": 1,
        "title": "Web Development Workshop",
        "description": "Tanuljunk modern web fejlesztést",
        "date": "2026-02-15T14:00:00.000000Z",
        "location": "Budapest, Tech Hub",
        "max_attendees": 30,
        "created_at": "2026-01-14T10:30:00.000000Z"
    },
    { ... }
]
```

##### 2. Előkövetkezõ Események
```
GET /events/upcoming
```
**Autentikáció**: Szükséges
**Leírás**: Jövőbeli események listája (mai naptól)
**Válasz** (200 OK): Eseménylista a mai naptól kezdve

##### 3. Múltbeli Események
```
GET /events/past
```
**Autentikáció**: Szükséges
**Leírás**: Már lezajlott események listája
**Válasz** (200 OK): Múltbeli eseménylista

##### 4. Események Szûrése
```
GET /events/filter
```
**Autentikáció**: Szükséges
**Leírás**: Események szûrése több kritérium alapján
**Query Paraméterek**:
- `location`: Hely szerinti szûrés
- `date_from`: Kezdõdátum (YYYY-MM-DD)
- `date_to`: Végdátum (YYYY-MM-DD)
- `search`: Keresés címben/leírásban

**Válasz** (200 OK): Szûrt eseménylista

##### 5. Esemény Létrehozása (Admin)
```
POST /events
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Új esemény létrehozása
**Request Body**:
```json
{
    "title": "Python Workshop",
    "description": "Python programozás alapjai",
    "date": "2026-02-20T15:00:00",
    "location": "Budapest",
    "max_attendees": 25
}
```

##### 6. Esemény Módosítása (Admin)
```
PUT /events/{id}
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Esemény adatainak módosítása
**Request Body**: Ugyanaz, mint a létrehozásnál

##### 7. Esemény Törlése (Admin)
```
DELETE /events/{id}
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Esemény törlése (soft delete)

#### Regisztrációs Végpontok

##### 1. Felhasználó Feliratkozása Eseményre
```
POST /events/{event}/register
```
**Autentikáció**: Szükséges
**Leírás**: Bejelentkezett felhasználó feliratkozása egy eseményre
**Paraméter**: `event` - Esemény ID
**Request Body** (opcionális):
```json
{
    "status": "elfogadva"
}
```

**Sikeres Válasz** (201 Created):
```json
{
    "message": "Successfully registered for event",
    "registration": {
        "id": 1,
        "user_id": 1,
        "event_id": 1,
        "status": "függőben",
        "registered_at": "2026-01-14T10:30:00.000000Z"
    }
}
```

**Hiba Válasz** (422 Unprocessable Entity):
```json
{
    "message": "Already registered for this event"
}
```

vagy

```json
{
    "message": "Event is full"
}
```

##### 2. Felhasználó Leiratkozása Eseményrõl
```
DELETE /events/{event}/unregister
```
**Autentikáció**: Szükséges
**Leírás**: Bejelentkezett felhasználó leiratkozása egy eseményrõl
**Paraméter**: `event` - Esemény ID

**Válasz** (200 OK):
```json
{
    "message": "Successfully unregistered from event"
}
```

##### 3. Admin Felhasználó Eltávolítása Eseménybõl
```
DELETE /events/{event}/users/{user}
```
**Autentikáció**: Szükséges
**Jogosultság**: Admin
**Leírás**: Admin által a felhasználó eltávolítása az eseményrõl
**Paraméterek**:
- `event` - Esemény ID
- `user` - Felhasználó ID

**Válasz** (200 OK):
```json
{
    "message": "User removed from event successfully"
}
```

---

## 7. HTTP Státusz Kódok és Hibakezelés

### Státusz Kódok

| Kód | Típus | Leírás |
|-----|-------|--------|
| 200 | OK | Sikeres kérés |
| 201 | Created | Erőforrás sikeresen létrehozva |
| 400 | Bad Request | Hibás kérés formátum |
| 401 | Unauthorized | Hitelesítés szükséges |
| 403 | Forbidden | Nincs jogosultság |
| 404 | Not Found | Erőforrás nem található |
| 422 | Unprocessable Entity | Validációs hiba |
| 500 | Internal Server Error | Szerver hiba |

### Hibás Válasz Formátum

```json
{
    "message": "Hiba leírása",
    "errors": {
        "field": ["Mező-specifikus hiba"]
    }
}
```

---

## 8. Modul Controllers és Végpontok

### 8.1 AuthController

**Fájl**: [app/Http/Controllers/Api/AuthController.php](app/Http/Controllers/Api/AuthController.php)

**Métódusok**:
1. `register(Request $request)` - Felhasználó regisztrációja
2. `login(Request $request)` - Bejelentkezés és token generálás

**Implementációs Részletek**:
- Jelszó titkosítás: `Hash::make()`
- Token generálás: Laravel Sanctum
- Validáció keretrendszer: Laravel Validator

---

### 8.2 UserController

**Fájl**: [app/Http/Controllers/Api/UserController.php](app/Http/Controllers/Api/UserController.php)

**Métódusok**:
1. `me()` - Saját profil lekérése
2. `updateMe(Request $request)` - Saját profil frissítése
3. `logout()` - Kijelentkezés
4. `index()` - Felhasználók listázása (Admin)
5. `show(User $user)` - Specifikus felhasználó lekérése
6. `store(Request $request)` - Felhasználó létrehozása (Admin)
7. `update(Request $request, User $user)` - Felhasználó módosítása (Admin)
8. `destroy(User $user)` - Felhasználó törlése (Admin)

---

### 8.3 EventController

**Fájl**: [app/Http/Controllers/Api/EventController.php](app/Http/Controllers/Api/EventController.php)

**Métódusok**:
1. `index()` - Események listázása
2. `upcoming()` - Jövõbeli események
3. `past()` - Múltbeli események
4. `filter()` - Szûrt események
5. `store(Request $request)` - Esemény létrehozása (Admin)
6. `update(Request $request, Event $event)` - Esemény módosítása (Admin)
7. `destroy(Event $event)` - Esemény törlése (Admin)

---

### 8.4 RegistrationController

**Fájl**: [app/Http/Controllers/Api/RegistrationController.php](app/Http/Controllers/Api/RegistrationController.php)

**Métódusok**:
1. `register(Request $request, Event $event)` - Felhasználó feliratkozása
2. `unregister(Event $event)` - Felhasználó leiratkozása
3. `adminRemoveUser(Event $event, User $user)` - Admin által eltávolítás

---

## 9. Modul Tesztelés

### 9.1 Tesztelési Stratégia

A projekt **PHPUnit** keretrendszert használ teszteléshez.

```bash
# Összes teszt futtatása
php artisan test

# Specifikus teszt fájl futtatása
php artisan test tests/Feature/AuthTest.php

# Tesztek futtatása verbose módban
php artisan test --verbose

# Teszt lefedettség
php artisan test --coverage
```

### 9.2 Teszt Szervezet

```
tests/
├── Feature/
│   ├── AuthTest.php              # Autentikáció tesztek
│   ├── EventTest.php             # Esemény végpontok tesztek
│   ├── UserTest.php              # Felhasználó végpontok tesztek
│   └── RegistrationTest.php      # Regisztrációs végpontok tesztek
└── Unit/
    ├── UserModelTest.php         # User model tesztek
    ├── EventModelTest.php        # Event model tesztek
    └── RegistrationModelTest.php # Registration model tesztek
```

### 9.3 Teszt Példák

#### 9.3.1 Autentikáció Teszt

```php
class AuthTest extends TestCase {
    
    public function test_user_can_register() {
        $response = $this->postJson('/api/register', [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password123',
            'phone' => '+36301234567'
        ]);
        
        $response->assertStatus(201)
                 ->assertJsonStructure(['message', 'user']);
    }
    
    public function test_user_can_login() {
        $user = User::factory()->create([
            'password' => Hash::make('password123')
        ]);
        
        $response = $this->postJson('/api/login', [
            'email' => $user->email,
            'password' => 'password123'
        ]);
        
        $response->assertStatus(200)
                 ->assertJsonStructure(['message', 'token']);
    }
}
```

#### 9.3.2 API Végpont Teszt

```php
class EventTest extends TestCase {
    
    public function test_authenticated_user_can_get_events() {
        $user = User::factory()->create();
        
        $response = $this->actingAs($user)
                         ->getJson('/api/events');
        
        $response->assertStatus(200)
                 ->assertJsonIsArray();
    }
    
    public function test_admin_can_create_event() {
        $admin = User::factory()->create(['is_admin' => true]);
        
        $response = $this->actingAs($admin)
                         ->postJson('/api/events', [
                             'title' => 'New Event',
                             'date' => '2026-02-15 14:00:00',
                             'location' => 'Budapest',
                             'max_attendees' => 30
                         ]);
        
        $response->assertStatus(201);
    }
}
```

#### 9.3.3 Model Relációk Teszt

```php
class UserModelTest extends TestCase {
    
    public function test_user_has_registrations() {
        $user = User::factory()->create();
        $event = Event::factory()->create();
        
        Registration::create([
            'user_id' => $user->id,
            'event_id' => $event->id,
            'status' => 'elfogadva'
        ]);
        
        $this->assertCount(1, $user->registrations);
    }
    
    public function test_user_has_many_events_through_registrations() {
        $user = User::factory()->create();
        $events = Event::factory(3)->create();
        
        foreach ($events as $event) {
            $user->registrations()->create([
                'event_id' => $event->id,
                'status' => 'elfogadva'
            ]);
        }
        
        $this->assertCount(3, $user->events);
    }
}
```

### 9.4 Teszt Futtatási Parancsok

```bash
# Fejlesztési szerverrel párhuzamos tesztelés
npm run dev

# Unit tesztek
php artisan test tests/Unit

# Feature tesztek
php artisan test tests/Feature

# Egyetlen teszt metódus
php artisan test tests/Feature/AuthTest.php::test_user_can_register

# Teszt szûrés név alapján
php artisan test --filter=test_user_can_login

# Teszt halmazás (grouping)
#@group auth
php artisan test --group=auth
```

---

## 10. API Végpontok

| HTTP Metódus | Végpont | Leírás | Auth | Admin |
|---|---|---|---|---|
| GET | `/ping` | API státusz ellenőrzés | ❌ | ❌ |
| POST | `/register` | Felhasználó regisztrációja | ❌ | ❌ |
| POST | `/login` | Bejelentkezés | ❌ | ❌ |
| GET | `/me` | Saját profil | ✅ | ❌ |
| PUT | `/me` | Profil módosítása | ✅ | ❌ |
| POST | `/logout` | Kijelentkezés | ✅ | ❌ |
| GET | `/users` | Felhasználók listája | ✅ | ✅ |
| POST | `/users` | Felhasználó létrehozása | ✅ | ✅ |
| GET | `/users/{id}` | Felhasználó lekérése | ✅ | ❌ |
| PUT | `/users/{id}` | Felhasználó módosítása | ✅ | ✅ |
| DELETE | `/users/{id}` | Felhasználó törlése | ✅ | ✅ |
| GET | `/events` | Események listája | ✅ | ❌ |
| GET | `/events/upcoming` | Jövõbeli események | ✅ | ❌ |
| GET | `/events/past` | Múltbeli események | ✅ | ❌ |
| GET | `/events/filter` | Szûrt események | ✅ | ❌ |
| POST | `/events` | Esemény létrehozása | ✅ | ✅ |
| PUT | `/events/{id}` | Esemény módosítása | ✅ | ✅ |
| DELETE | `/events/{id}` | Esemény törlése | ✅ | ✅ |
| POST | `/events/{event}/register` | Feliratkozás | ✅ | ❌ |
| DELETE | `/events/{event}/unregister` | Leiratkozás | ✅ | ❌ |
| DELETE | `/events/{event}/users/{user}` | Admin eltávolítás | ✅ | ✅ |

---

## 11. Felhasználói Permissions

| Művelet | Guest | Felhasználó | Admin |
|---------|-------|------------|-------|
| Regisztrálás | ✅ | ❌ | ❌ |
| Bejelentkezés | ✅ | ❌ | ❌ |
| Saját profil megtekintése | ❌ | ✅ | ✅ |
| Saját profil módosítása | ❌ | ✅ | ✅ |
| Események megtekintése | ❌ | ✅ | ✅ |
| Eseményre feliratkozás | ❌ | ✅ | ✅ |
| Felhasználók megtekintése | ❌ | ❌ | ✅ |
| Felhasználók kezelése | ❌ | ❌ | ✅ |
| Események kezelése (CRUD) | ❌ | ❌ | ✅ |
| Regisztrációk kezelése | ❌ | ❌ | ✅ |

---

## 12. Telepítés és Futtatás

### 12.1 Előfeltételek
- PHP 8.2+
- Node.js 16+
- Composer
- MySQL/PostgreSQL

### 12.2 Telepítési Lépések

```bash
# Projekt letöltése
git clone <repository>
cd eventregistration_devmain-main

# Composer függőségek
composer install

# Environment fájl másolása
cp .env.example .env

# Alkalmazás kulcs generálása
php artisan key:generate

# Adatbázis migrációk futtatása
php artisan migrate

# NPM függőségek és build
npm install
npm run build

# Development szerver indítása
npm run dev
```

### 12.3 Quick Setup Script

```bash
composer run setup
```

---

## 13. Postman Collection

A teljes API tesztelhető Postman-ben. Az alábbi összefoglaló segítségével:

```json
{
    "info": {
        "name": "Event Registration API",
        "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
    },
    "item": [
        {
            "name": "Auth",
            "item": [
                {
                    "name": "Register",
                    "request": {
                        "method": "POST",
                        "url": "{{base_url}}/register"
                    }
                },
                {
                    "name": "Login",
                    "request": {
                        "method": "POST",
                        "url": "{{base_url}}/login"
                    }
                }
            ]
        }
    ],
    "variable": [
        {
            "key": "base_url",
            "value": "http://localhost:8000/api"
        },
        {
            "key": "token",
            "value": ""
        }
    ]
}
```

---

## 14. Hibakezelés

### 401 Unauthorized
**Ok**: Hiányzó vagy érvénytelen token
**Megoldás**: Lépjen be vagy ellenőrizze az Authorization header értékét

### 403 Forbidden
**Ok**: Nincs elegendő jogosultság (nem admin)
**Megoldás**: Kérje az admin jogosultságot

### 422 Validation Error
**Ok**: Validációs hibák az input adatban
**Megoldás**: Ellenőrizze az error objektumot és javítsa az adatokat

### 404 Not Found
**Ok**: Az erőforrás nem létezik
**Megoldás**: Ellenőrizze az ID és az URL helyességét

### Duplicate Entry Hiba
**Ok**: A felhasználó már regisztrálva van az eseményre
**Megoldás**: Előbb leiratkozni, majd újra feliratkozni

---
