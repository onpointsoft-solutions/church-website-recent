# Quick Fix for Java Version Error

## Problem
Error: `Unsupported class file major version 65`

This means you're using Java 21, but the Gradle version doesn't support it.

## Solution

The project has been updated to support Java 21. Follow these steps:

### Step 1: Stop Gradle Daemon
```cmd
cd android_app
gradlew --stop
```

### Step 2: Close Android Studio
Close Android Studio completely to release all file locks.

### Step 3: Clear Gradle Cache (Optional but Recommended)
After closing Android Studio:
```cmd
rmdir /s /q .gradle
rmdir /s /q %USERPROFILE%\.gradle\caches\7.5
```

If you get "file in use" errors, that's okay - proceed to the next step.

### Step 4: Reopen Android Studio
1. Open Android Studio
2. Open the `android_app` project
3. Wait for Gradle sync to complete (it will download Gradle 8.0)
4. The project should now build successfully

### Step 5: Build the Project
In Android Studio:
- Click **Build** > **Make Project** (Ctrl+F9)

Or from command line:
```cmd
gradlew build
```

## What Was Changed

The following files were updated to support Java 21:

1. **gradle/wrapper/gradle-wrapper.properties**
   - Upgraded from Gradle 7.5 to Gradle 8.0

2. **build.gradle**
   - Upgraded Android Gradle Plugin from 7.4.2 to 8.0.2
   - Upgraded Kotlin from 1.8.22 to 1.9.0

3. **app/build.gradle.kts**
   - Already configured correctly for the new versions

## Alternative: Use Java 17

If you prefer not to use Java 21, you can:

1. Install Java 17 (LTS version)
2. Set JAVA_HOME to Java 17
3. Restart Android Studio

The project will work with Java 17 as well.

## Verify Java Version

Check your Java version:
```cmd
java -version
```

You should see either:
- `openjdk version "17.x.x"` or
- `openjdk version "21.x.x"`

## Still Having Issues?

1. Make sure Android Studio is completely closed
2. Delete these folders:
   - `android_app\.gradle`
   - `android_app\app\build`
3. Restart your computer (to release all file locks)
4. Open Android Studio and sync again

## Next Steps

Once the build succeeds:
1. Run the app on an emulator or device
2. Test gallery upload functionality
3. Test sermon management features
4. Configure API base URL in `RetrofitClient.kt`
