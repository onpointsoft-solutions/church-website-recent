# CEFC Volunteers Mobile App

A modern, production-ready Android application for managing volunteers and sermons at Christ Ekklesia Fellowship Chapel.

## Quick Start

### Requirements
- Android Studio Arctic Fox or later
- Android SDK 24+
- Kotlin 1.7+

### Build & Run
```bash
# Clone repository
git clone <repo-url>
cd android_app

# Build
./gradlew build

# Run on device/emulator
./gradlew installDebug
```

## Features

✅ **User Authentication**
- Secure login/signup
- OTP verification
- Session management

✅ **Sermon Management**
- Browse all sermons
- Search functionality
- Filter by ministry
- View sermon details

✅ **Admin Features**
- Upload sermons with videos
- Add thumbnails
- Edit sermon details
- Delete sermons

✅ **User Features**
- Favorite sermons
- Download for offline viewing
- Share sermons
- View history

✅ **Technical Features**
- Offline support with Room database
- Encrypted data storage
- Material Design 3 UI
- Smooth animations
- Responsive layouts

## Project Structure

```
src/main/
├── kotlin/com/cefc/volunteers/
│   ├── data/          # Data layer (API, database, repositories)
│   ├── ui/            # UI layer (activities, adapters)
│   └── util/          # Utilities (security, preferences, files)
└── res/
    ├── layout/        # XML layouts
    ├── values/        # Colors, strings, themes
    └── drawable/      # Icons and images
```

## Key Technologies

- **Kotlin** - Modern Android development
- **Retrofit 2** - HTTP client
- **Room** - Local database
- **Coroutines** - Async operations
- **Material Design 3** - UI components
- **Glide** - Image loading
- **Encrypted SharedPreferences** - Secure storage

## Configuration

### API Endpoint
Edit `RetrofitClient.kt`:
```kotlin
private const val BASE_URL = "https://your-domain.com/"
```

### Database
Automatically created on first run. Located in app's internal storage.

## API Endpoints

### Authentication
- `POST /volunteers_api.php` - Login
- `POST /volunteers_api.php` - Signup
- `POST /volunteers_api.php` - Logout

### Sermons
- `GET /sermons_api_enhanced.php` - Get all sermons
- `GET /sermons_api_enhanced.php?id=<id>` - Get sermon details
- `POST /sermons_api_enhanced.php` - Upload sermon
- `POST /sermons_api_enhanced.php` - Update sermon
- `POST /sermons_api_enhanced.php` - Delete sermon

## Usage

### Login
1. Enter email and password
2. Tap "Sign In"
3. Credentials are securely stored

### Browse Sermons
1. View sermon list on home screen
2. Pull to refresh
3. Tap sermon to view details

### Upload Sermon (Admin)
1. Tap "Upload Sermon"
2. Fill in details
3. Select video file
4. (Optional) Select thumbnail
5. Tap "Upload"

### Manage Sermons
- **Favorite**: Tap heart icon
- **Download**: Tap download button
- **Share**: Tap share button
- **Details**: Tap sermon card

## Security

- Encrypted storage for credentials
- HTTPS-only API communication
- CSRF token protection
- Rate limiting on login
- Input validation and sanitization

## Troubleshooting

### App won't start
- Clear cache: Settings > Apps > CEFC Volunteers > Storage > Clear Cache
- Reinstall the app
- Check Android version (min API 24)

### Login fails
- Verify credentials
- Check internet connection
- Ensure account is verified by admin

### Sermons won't load
- Check internet connection
- Pull down to refresh
- Verify API endpoint is correct

### Upload fails
- Check file size (max 500MB)
- Verify video format (MP4, MOV, AVI)
- Ensure sufficient storage

## Testing

```bash
# Unit tests
./gradlew test

# Instrumented tests
./gradlew connectedAndroidTest

# Build release APK
./gradlew assembleRelease

# Build App Bundle
./gradlew bundleRelease
```

## Documentation

See [ANDROID_APP_DOCUMENTATION.md](../ANDROID_APP_DOCUMENTATION.md) for comprehensive documentation.

## Support

For issues and support:
- Email: support@onpointsoftwares.com
- GitHub Issues: [project-url]/issues

## License

© 2024 Onpoint Softwares Solutions. All rights reserved.

## Version

Current Version: 1.0.0
