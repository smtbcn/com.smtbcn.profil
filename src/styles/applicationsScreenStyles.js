import { StyleSheet } from 'react-native';

export const applicationsScreenStyles = StyleSheet.create({
    container: {
        flex: 1,
    },
    scrollContent: {
        paddingBottom: 24,
    },
    headerContainer: {
        paddingHorizontal: 16,
        paddingTop: 24,
        paddingBottom: 16,
        alignItems: 'center',
    },
    title: {
        fontSize: 24,
        fontWeight: '700',
    },
    appsContainer: {
        paddingHorizontal: 16,
        marginTop: 16,
        gap: 16,
    },
    appCard: {
        flexDirection: 'row',
        alignItems: 'center',
        borderWidth: 1,
        borderRadius: 12,
        padding: 16,
        borderRadius: 12,
        padding: 16,
    },
    appCardPressed: {
        opacity: 0.7,
    },
    iconContainer: {
        marginRight: 16,
    },

    appInfo: {
        flex: 1,
    },
    appName: {
        fontSize: 18,
        fontWeight: '600',
        marginBottom: 4,
    },
    appDescription: {
        fontSize: 14,
        lineHeight: 20,
    },
});
