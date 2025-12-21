import React, { useState, useEffect } from 'react';
import { View, StyleSheet, Dimensions } from 'react-native';
import { WebView } from 'react-native-webview';
import { useTranslation } from 'react-i18next';
import { useTheme } from '../theme';
import { SkeletonItem } from './SkeletonItem';
import { ProfileService } from '../services/apiService';

export const AboutInfo: React.FC = () => {
  const { colors, isDark } = useTheme();
  const { i18n } = useTranslation();
  const [aboutText, setAboutText] = useState('');
  const [loading, setLoading] = useState(true);
  const [webViewHeight, setWebViewHeight] = useState(200);

  const fetchAbout = async () => {
    const data = await ProfileService.getProfileData();
    if (data && data.about) {
      const text = i18n.language === 'tr' ? data.about.tr : data.about.en;
      setAboutText(text || '');
    }
    setLoading(false);
  };

  useEffect(() => {
    fetchAbout();
  }, [i18n.language]);

  // HTML içeriğini WebView için sarmala
  const htmlContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <meta name="viewport" content="width=device-width, initial-scale=1.3, maximum-scale=1.0, user-scalable=no">
            <style>
                body {
                    background-color: transparent;
                    color: ${colors.text};
                    font-family: -apple-system, system-ui;
                    font-size: 16px;
                    line-height: 1.6;
                    margin: 0;
                    padding: 0;
                }
                h1, h2, h3 { color: ${colors.text}; margin-top: 10px; margin-bottom: 10px; }
                p { margin-bottom: 15px; }
                strong { font-weight: bold; color: ${colors.primary}; }
                a { color: ${colors.primary}; text-decoration: none; }
                li { margin-bottom: 5px; }
            </style>
        </head>
        <body>
            <div id="content">${aboutText}</div>
            <script>
                // İçerik yüksekliğini ölçüp React Native'e bildir
                function sendHeight() {
                    window.ReactNativeWebView.postMessage(
                        JSON.stringify({height: document.body.scrollHeight})
                    );
                }
                window.onload = sendHeight;
                window.onresize = sendHeight;
            </script>
        </body>
        </html>
    `;

  if (loading) {
    return (
      <View style={{ padding: 20 }}>
        <SkeletonItem height={20} width="90%" />
        <SkeletonItem height={20} width="100%" />
        <SkeletonItem height={20} width="80%" />
      </View>
    );
  }

  if (!aboutText || aboutText === '') return null;

  return (
    <View style={[styles.container, { height: webViewHeight }]}>
      <WebView
        originWhitelist={['*']}
        source={{ html: htmlContent }}
        style={styles.webView}
        scrollEnabled={false}
        backgroundColor="transparent"
        onMessage={(event) => {
          const data = JSON.parse(event.nativeEvent.data);
          if (data.height) {
            setWebViewHeight(data.height + 20);
          }
        }}
      />
    </View>
  );
};

const styles = StyleSheet.create({
  container: {
    paddingHorizontal: 20,
    paddingBottom: 20,
    overflow: 'hidden',
  },
  webView: {
    backgroundColor: 'transparent',
  },
});
