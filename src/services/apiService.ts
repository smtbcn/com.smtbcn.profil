import axios from 'axios';

// Backend URL
const API_BASE_URL = 'https://profil.milasoft.com.tr/backend/api';
const API_KEY = 'milasoft_secure_key_2025';

const apiClient = axios.create({
    baseURL: API_BASE_URL,
    headers: {
        'X-API-KEY': API_KEY,
        'Content-Type': 'application/json',
    },
});

export const ProfileService = {
    getProfileData: async () => {
        try {
            const response = await apiClient.get('/profile.php');
            return response.data;
        } catch (error) {
            console.error('API Error (Profile):', error);
            return null;
        }
    },
    getApps: async () => {
        try {
            const response = await apiClient.get('/apps.php');
            return response.data;
        } catch (error) {
            console.error('API Error (Apps):', error);
            return null;
        }
    },
    getProjects: async () => {
        try {
            const response = await apiClient.get('/projects.php');
            return response.data;
        } catch (error) {
            console.error('API Error (Projects):', error);
            return null;
        }
    },
};
