# CEFC Volunteers Android App - Complete Documentation

## Overview

A production-level Android application for the Christ Ekklesia Fellowship Chapel (CEFC) volunteers portal, featuring sermon management, user authentication, and offline capabilities.

## Features

### Core Features
- **User Authentication**: Secure login/signup with OTP verification
- **Sermon Management**: Browse, search, favorite, and download sermons
- **Admin Upload**: Upload sermons with video files and thumbnails
- **Offline Access**: Download sermons for offline viewing
- **Local Database**: Room database for caching and offline functionality
- **Secure Storage**: Encrypted SharedPreferences for sensitive data

### UI/UX Features
- Material Design 3 compliant
- Responsive layouts for all screen sizes
- Smooth animations and transitions
- Dark mode support ready
- Accessibility features (ARIA labels, semantic HTML)

## Project Structure

```
android_app/
├── build.gradle.kts                 # Project build configuration
├── AndroidManifest.xml              # App manifest
├── src/main/
│   ├── kotlin/com/cefc/volunteers/
│   │   ├── data/
│   │   │   ├── api/
│   │   │   │   ├── ApiService.kt    # Retrofit API interface
│   │   │   │   └── RetrofitClient.kt # Retrofit configuration
│   │   │   ├── local/
│   │   │   │   ├── AppDatabase.kt   # Room database
│   │   │   │   ├── Entities.kt      # Database entities
│   │   │   │   └── Daos.kt          # Data access objects
│   │   │   ├── model/
│   │   │   │   └── Models.kt        # Data models
│   │   │   └── repository/
│   │   │       ├── AuthRepository.kt # Authentication logic
│   │   │       └── SermonRepository.kt # Sermon management
│   │   ├── ui/
│   │   │   ├── activity/
│   │   │   │   ├── SplashActivity.kt
│   │   │   │   ├── LoginActivity.kt
│   │   │   │   ├── SignupActivity.kt
│   │   │   │   ├── MainActivity.kt
│   │   │   │   ├── UploadSermonActivity.kt
│   │   │   │   └── SermonDetailActivity.kt
│   │   │   └── adapter/
│   │   │       └── SermonAdapter.kt
│   │   └── util/
│   │       ├── SecurityManager.kt    # Encryption & security
│   │       ├── PreferenceManager.kt  # Preferences
│   │       └── FileUtils.kt          # File operations
│   └── res/
│       ├── layout/                   # XML layouts
│       ├── values/                   # Resources (colors, strings, themes)
│       └── drawable/                 # Icons and images
```

## Technology Stack

### Core Libraries
- **Kotlin**: Modern Android development language
- **Android Architecture Components**: LiveData, ViewModel, Room
- **Retrofit 2**: HTTP client for API calls
- **OkHttp 3**: HTTP interceptor and logging
- **Coroutines**: Asynchronous programming
- **Room Database**: Local data persistence
- **Material Design 3**: UI components and theming

### Security
- **Encrypted SharedPreferences**: Secure data storage
- **MasterKey**: Encryption key management
- **HTTPS**: Secure API communication

### Image Loading
- **Glide**: Image loading and caching

## API Endpoints

### Authentication
- `POST /volunteers_api.php` - Login
- `POST /volunteers_api.php` - Signup
- `POST /volunteers_api.php` - Verify OTP
- `POST /volunteers_api.php` - Logout

### Sermons
- `GET /sermons_api_enhanced.php` - Get all sermons
- `GET /sermons_api_enhanced.php?id=<id>` - Get sermon details
- `POST /sermons_api_enhanced.php` - Upload sermon (admin)
- `POST /sermons_api_enhanced.php` - Update sermon (admin)
- `POST /sermons_api_enhanced.php` - Delete sermon (admin)
- `GET /sermons_api_enhanced.php?search=<query>` - Search sermons

## Setup Instructions

### Prerequisites
- Android Studio Arctic Fox or later
- Android SDK 24 (API level 24) or higher
- Kotlin 1.7+
- Gradle 7.0+

### Installation Steps

1. **Clone the project**
   ```bash
   git clone <repository-url>
   cd android_app
   ```

2. **Configure API Base URL**
   - Edit `RetrofitClient.kt`
   - Update `BASE_URL` to your server URL
   ```kotlin
   private const val BASE_URL = "https://your-domain.com/"
   ```

3. **Build the project**
   ```bash
   ./gradlew build
   ```

4. **Run on emulator or device**
   ```bash
   ./gradlew installDebug
   ```

## Configuration

### Database Configuration
The app uses Room database with automatic migrations. Database is created automatically on first run.

### API Configuration
- Base URL: `https://cefc.onpointsoftwares.com/`
- Timeout: 30 seconds (configurable in `RetrofitClient.kt`)
- Logging: HTTP logging enabled in debug builds

### Security Configuration
- CSRF token generation for API requests
- Encrypted storage for authentication credentials
- Session management with timeout

## Usage Guide

### User Authentication

#### Login
1. Launch the app
2. Enter email and password
3. Tap "Sign In"
4. App validates credentials and caches user data

#### Signup
1. Tap "Sign Up" on login screen
2. Fill in all required fields
3. Tap "Create Account"
4. Wait for admin verification

### Sermon Management

#### Browse Sermons
1. Open the app (logged in)
2. Sermons are displayed in a list
3. Pull down to refresh
4. Tap on a sermon to view details

#### Upload Sermon (Admin)
1. Tap "Upload Sermon" button
2. Fill in sermon details:
   - Title
   - Speaker name
   - Ministry
   - Date
   - Description
3. Select video file
4. (Optional) Select thumbnail image
5. Tap "Upload"

#### Manage Sermons
- **Favorite**: Tap heart icon to add/remove from favorites
- **Download**: Download sermon for offline viewing
- **Share**: Share sermon via other apps
- **View Details**: Tap sermon card for full details

## API Response Examples

### Get Sermons
```json
{
  "success": true,
  "sermons": [
    {
      "id": 1,
      "title": "Faith in Action",
      "speaker": "Pastor John",
      "date": "2024-01-15",
      "ministry": "Worship Team",
      "description": "A powerful message about faith",
      "thumbnail": "uploads/thumbnails/thumb_123.jpg",
      "file_url": "uploads/sermons/sermon_123.mp4",
      "views": 150
    }
  ],
  "total": 1
}
```

### Upload Sermon
```json
{
  "success": true,
  "message": "Sermon uploaded successfully",
  "id": 2,
  "file_url": "uploads/sermons/sermon_456.mp4",
  "thumbnail_url": "uploads/thumbnails/thumb_456.jpg"
}
```

## Database Schema

### Sermons Table
```sql
CREATE TABLE sermons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    speaker VARCHAR(100) NOT NULL,
    ministry VARCHAR(100),
    date DATE NOT NULL,
    description TEXT,
    file_url VARCHAR(255),
    thumbnail VARCHAR(255),
    youtube VARCHAR(255),
    duration VARCHAR(20),
    views INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

### Local Sermon Entity
```kotlin
@Entity(tableName = "sermons")
data class SermonEntity(
    @PrimaryKey val id: Int,
    val title: String,
    val speaker: String,
    val date: String,
    val ministry: String? = null,
    val description: String? = null,
    val thumbnail: String? = null,
    val fileUrl: String? = null,
    val isDownloaded: Boolean = false,
    val localPath: String? = null,
    val isFavorite: Boolean = false,
    val syncedAt: Long = System.currentTimeMillis()
)
```

## Security Considerations

### Authentication
- Passwords are hashed using bcrypt on the server
- CSRF tokens prevent cross-site attacks
- Rate limiting on login attempts (5 attempts, 15-minute lockout)

### Data Storage
- Sensitive data encrypted using Android Security library
- No plain-text credentials stored
- Encrypted SharedPreferences for tokens

### API Communication
- HTTPS only (no cleartext traffic)
- Certificate pinning ready (can be implemented)
- Request/response logging in debug builds only

### File Handling
- File type validation (video/image)
- File size limits enforced
- Secure file storage in app cache

## Performance Optimization

### Caching
- Room database caches sermons locally
- Glide caches images in memory and disk
- Retrofit response caching enabled

### Network
- Connection timeout: 30 seconds
- Read timeout: 30 seconds
- Write timeout: 30 seconds

### UI
- RecyclerView with DiffUtil for efficient list updates
- Lazy loading of images
- Coroutines for non-blocking operations

## Troubleshooting

### Common Issues

#### Login fails with "Invalid email or password"
- Verify email and password are correct
- Check internet connection
- Ensure account is verified by admin

#### Sermon upload fails
- Check file size (max 500MB)
- Verify video format (MP4, MOV, AVI)
- Ensure sufficient storage space
- Check internet connection

#### App crashes on startup
- Clear app cache: Settings > Apps > CEFC Volunteers > Storage > Clear Cache
- Uninstall and reinstall the app
- Check Android version compatibility (min API 24)

#### Sermons not loading
- Check internet connection
- Pull down to refresh
- Verify API endpoint is accessible
- Check server logs for errors

## Testing

### Unit Tests
```bash
./gradlew test
```

### Instrumented Tests
```bash
./gradlew connectedAndroidTest
```

### Manual Testing Checklist
- [ ] Login with valid credentials
- [ ] Signup with new account
- [ ] Browse sermons list
- [ ] Search for sermons
- [ ] View sermon details
- [ ] Upload sermon (admin)
- [ ] Download sermon
- [ ] Add to favorites
- [ ] Share sermon
- [ ] Logout
- [ ] Test offline mode

## Deployment

### Build Release APK
```bash
./gradlew assembleRelease
```

### Build App Bundle (for Play Store)
```bash
./gradlew bundleRelease
```

### Signing Configuration
Create `keystore.properties` in project root:
```properties
storeFile=path/to/keystore.jks
storePassword=your_password
keyAlias=your_alias
keyPassword=your_key_password
```

## Maintenance

### Regular Tasks
- Monitor API logs for errors
- Review crash reports
- Update dependencies quarterly
- Test with new Android versions
- Backup user data

### Version Updates
- Follow semantic versioning
- Update version code in `build.gradle.kts`
- Document changes in release notes

## Support and Contact

For issues, feature requests, or support:
- Email: support@onpointsoftwares.com
- GitHub Issues: [project-url]/issues
- Documentation: [wiki-url]

## License

Copyright © 2024 Onpoint Softwares Solutions. All rights reserved.

## Changelog

### Version 1.0.0 (Initial Release)
- User authentication (login/signup)
- Sermon browsing and search
- Sermon upload (admin)
- Offline sermon access
- Favorites management
- Material Design UI
- Secure data storage
