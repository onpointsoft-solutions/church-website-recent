package com.cefc.volunteers.data.repository

import com.cefc.volunteers.data.api.ApiService
import com.cefc.volunteers.data.local.AppDatabase
import com.cefc.volunteers.data.local.UserEntity
import com.cefc.volunteers.data.model.LoginResponse
import com.cefc.volunteers.util.SecurityManager
import kotlinx.coroutines.flow.Flow
import kotlinx.coroutines.flow.flow

class AuthRepository(
    private val apiService: ApiService,
    private val database: AppDatabase,
    private val securityManager: SecurityManager
) {

    fun login(email: String, password: String): Flow<Result<LoginResponse>> = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()
            val response = apiService.login(
                email = email,
                password = password,
                csrfToken = csrfToken
            )

            if (response.isSuccessful && response.body()?.success == true) {
                response.body()?.user?.let { user ->
                    // Save user to local database
                    val userEntity = UserEntity(
                        id = user.id,
                        name = user.name,
                        email = user.email,
                        phone = user.phone,
                        ministry = user.ministry,
                        role = user.role,
                        joinDate = user.joinDate,
                        avatar = user.avatar,
                        lastLogin = user.lastLogin,
                        isLoggedIn = true
                    )
                    database.userDao().insertUser(userEntity)

                    // Save auth token
                    securityManager.saveAuthToken(email, password)
                }
                emit(Result.success(response.body()!!))
            } else {
                emit(Result.failure(Exception(response.body()?.message ?: "Login failed")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun signup(
        name: String,
        email: String,
        phone: String,
        ministry: String,
        password: String
    ) = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()
            val response = apiService.signup(
                name = name,
                email = email,
                phone = phone,
                ministry = ministry,
                password = password,
                csrfToken = csrfToken
            )

            if (response.isSuccessful) {
                emit(Result.success(response.body()!!))
            } else {
                emit(Result.failure(Exception(response.body()?.message ?: "Signup failed")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun verifyOtp(email: String, otp: String) = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()
            val response = apiService.verifyOtp(
                email = email,
                otp = otp,
                csrfToken = csrfToken
            )

            if (response.isSuccessful) {
                emit(Result.success(response.body()!!))
            } else {
                emit(Result.failure(Exception(response.body()?.message ?: "Verification failed")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun logout() = flow {
        try {
            val csrfToken = securityManager.getCsrfToken()
            val response = apiService.logout(csrfToken = csrfToken)

            if (response.isSuccessful) {
                // Clear local data
                database.userDao().deleteAllUsers()
                securityManager.clearAuthToken()
                emit(Result.success(response.body()!!))
            } else {
                emit(Result.failure(Exception("Logout failed")))
            }
        } catch (e: Exception) {
            emit(Result.failure(e))
        }
    }

    fun getCurrentUser() = database.userDao().getCurrentUser()

    fun isLoggedIn() = database.userDao().getCurrentUser()
}
