# Android App Build Instructions

## Project Structure
The Android app is now properly organized with the following structure:
```
android_app/
├── app/                          # App module
│   ├── build.gradle.kts         # App-level build configuration
│   └── src/
│       └── main/
│           ├── AndroidManifest.xml
│           ├── kotlin/          # Kotlin source files
│           └── res/             # Resources
├── build.gradle                 # Root-level build configuration
├── settings.gradle.kts          # Project settings
├── gradle.properties            # Gradle properties
└── gradle/
    └── wrapper/
        ├── gradle-wrapper.properties
        └── gradle-wrapper.jar

```

## Build Configuration

### Versions
- **Android Gradle Plugin**: 8.0.2
- **Kotlin**: 1.9.0
- **Gradle**: 8.0
- **Compile SDK**: 33
- **Target SDK**: 33
- **Min SDK**: 24

### Prerequisites
1. **Java Development Kit (JDK)**: JDK 17 or JDK 21 (recommended)
2. **Android Studio**: Flamingo (2022.2.1) or later
3. **Android SDK**: API Level 33

## Building the App

### Option 1: Using Android Studio
1. Open Android Studio
2. Select "Open an Existing Project"
3. Navigate to `android_app` folder
4. Wait for Gradle sync to complete
5. Click "Build" > "Make Project" or press `Ctrl+F9`

### Option 2: Using Command Line

#### On Windows:
```cmd
cd android_app
gradlew.bat build
```

#### On Linux/Mac:
```bash
cd android_app
./gradlew build
```

### Build Variants
- **Debug**: `gradlew assembleDebug`
- **Release**: `gradlew assembleRelease`

## Running the App

### Using Android Studio
1. Connect an Android device or start an emulator
2. Click the "Run" button or press `Shift+F10`

### Using Command Line
```cmd
gradlew installDebug
```

## Troubleshooting

### Issue: "Unsupported class file major version 65"
**Solution**: 
This error occurs when using Java 21 with an incompatible Gradle version.
- **Option 1 (Recommended)**: The project has been updated to use Gradle 8.0 and AGP 8.0.2 which support Java 21
- **Option 2**: Downgrade to Java 17 or Java 11
- Stop all Gradle daemons: `gradlew --stop`
- Clear Gradle cache and rebuild

### Issue: Gradle sync fails
**Solution**: 
- Ensure you have JDK 11 or higher installed (JDK 17 or 21 recommended)
- Check internet connection (Gradle needs to download dependencies)
- Stop Gradle daemon: `gradlew --stop`
- Delete `.gradle` folder in project and sync again

### Issue: Build fails with "SDK not found"
**Solution**:
- Create/update `local.properties` file with:
  ```
  sdk.dir=C\:\\Users\\YourUsername\\AppData\\Local\\Android\\Sdk
  ```

### Issue: Kotlin plugin version mismatch
**Solution**:
- The project uses Kotlin 1.8.22
- Ensure Android Studio Kotlin plugin matches this version

### Issue: Out of memory during build
**Solution**:
- Increase heap size in `gradle.properties`:
  ```
  org.gradle.jvmargs=-Xmx4096m -Dfile.encoding=UTF-8
  ```

## Clean Build
If you encounter persistent issues, perform a clean build:
```cmd
gradlew clean
gradlew build
```

## APK Location
After successful build, APK files are located at:
- **Debug**: `app/build/outputs/apk/debug/app-debug.apk`
- **Release**: `app/build/outputs/apk/release/app-release.apk`

## Key Features Implemented
✅ User authentication (login/signup)
✅ Sermon management (view, upload, edit, delete)
✅ Gallery management (view, upload, delete)
✅ Category filtering
✅ Offline support with Room database
✅ Secure credential storage
✅ Material Design 3 UI

## API Configuration
Update the base URL in `RetrofitClient.kt`:
```kotlin
private const val BASE_URL = "https://your-domain.com/"
```

## Permissions Required
The app requires the following permissions (already declared in AndroidManifest.xml):
- `INTERNET` - For API communication
- `READ_EXTERNAL_STORAGE` - For image selection
- `WRITE_EXTERNAL_STORAGE` - For file downloads (API < 29)

## Next Steps
1. Test the app on a physical device or emulator
2. Configure the API base URL for your server
3. Test gallery upload functionality
4. Test sermon management features
5. Verify offline functionality

## Support
For issues or questions:
- Check the main README.md
- Review ANDROID_APP_DOCUMENTATION.md
- Contact: support@onpointsoftwares.com
