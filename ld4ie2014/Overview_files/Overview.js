// Created by iWeb 3.0.4 local-build-20141120

setTransparentGifURL('Media/transparent.gif');function applyEffects()
{var registry=IWCreateEffectRegistry();registry.registerEffects({stroke_0:new IWEmptyStroke(),stroke_1:new IWStrokeParts([{rect:new IWRect(-2,2,4,234),url:'Overview_files/stroke.png'},{rect:new IWRect(-2,-2,4,4),url:'Overview_files/stroke_1.png'},{rect:new IWRect(2,-2,353,4),url:'Overview_files/stroke_2.png'},{rect:new IWRect(355,-2,4,4),url:'Overview_files/stroke_3.png'},{rect:new IWRect(355,2,4,234),url:'Overview_files/stroke_4.png'},{rect:new IWRect(355,236,4,4),url:'Overview_files/stroke_5.png'},{rect:new IWRect(2,236,353,4),url:'Overview_files/stroke_6.png'},{rect:new IWRect(-2,236,4,4),url:'Overview_files/stroke_7.png'}],new IWSize(357,238))});registry.applyEffects();}
function hostedOnDM()
{return false;}
function onPageLoad()
{loadMozillaCSS('Overview_files/OverviewMoz.css')
adjustLineHeightIfTooBig('id1');adjustFontSizeIfTooBig('id1');adjustLineHeightIfTooBig('id2');adjustFontSizeIfTooBig('id2');detectBrowser();adjustLineHeightIfTooBig('id3');adjustFontSizeIfTooBig('id3');adjustLineHeightIfTooBig('id4');adjustFontSizeIfTooBig('id4');adjustLineHeightIfTooBig('id5');adjustFontSizeIfTooBig('id5');Widget.onload();fixupAllIEPNGBGs();fixAllIEPNGs('Media/transparent.gif');applyEffects()}
function onPageUnload()
{Widget.onunload();}
