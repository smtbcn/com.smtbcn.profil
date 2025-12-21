import { View, Image, Animated } from 'react-native';
import { useEffect, useRef } from 'react';
import { splashScreenStyles } from '../styles/splashScreenStyles';

export const SplashScreen = ({ onFinish }) => {
  const fadeAnim = useRef(new Animated.Value(0)).current;
  const scaleAnim = useRef(new Animated.Value(0.8)).current;

  useEffect(() => {
    Animated.parallel([
      Animated.timing(fadeAnim, {
        toValue: 1,
        duration: 800,
        useNativeDriver: true,
      }),
      Animated.spring(scaleAnim, {
        toValue: 1,
        tension: 50,
        friction: 7,
        useNativeDriver: true,
      }),
    ]).start();

    const timer = setTimeout(() => {
      Animated.timing(fadeAnim, {
        toValue: 0,
        duration: 500,
        useNativeDriver: true,
      }).start(() => {
        onFinish();
      });
    }, 2500);

    return () => clearTimeout(timer);
  }, []);

  return (
    <View style={splashScreenStyles.container}>
      <Animated.View
        style={[
          splashScreenStyles.logoWrapper,
          {
            opacity: fadeAnim,
            transform: [{ scale: scaleAnim }],
          },
        ]}
      >
        <View style={splashScreenStyles.logoFrame}>
          <Image
            source={require('../../assets/splash-icon.png')}
            style={splashScreenStyles.logo}
            resizeMode="contain"
          />
        </View>
      </Animated.View>
    </View>
  );
};
